<?php

declare(strict_types=1);

/**
 * @return array{status: int, body: array<string, mixed>, headers: list<string>}
 */
function requestApi(string $url, string $path, string $method = 'GET', ?string $body = null): array
{
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => "Content-Type: application/json\r\nConnection: close\r\n",
            'content' => $body ?? '',
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $raw = @file_get_contents($url . $path, false, $context);
    $headers = $http_response_header ?? [];

    if ($raw === false || !preg_match('#^HTTP/\S+ (\d{3})#', $headers[0] ?? '', $match)) {
        throw new RuntimeException("Request failed: $method $path");
    }

    $hasJsonHeader = false;
    foreach ($headers as $header) {
        if (stripos($header, 'Content-Type: application/json') === 0) {
            $hasJsonHeader = true;
        }
    }

    if (!$hasJsonHeader) {
        throw new RuntimeException("Expected a JSON response: $method $path");
    }

    $decoded = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        throw new RuntimeException("Expected a JSON object: $method $path");
    }

    return ['status' => (int) $match[1], 'body' => $decoded, 'headers' => $headers];
}

/** @return resource */
function startServer(string $directory, int $port)
{
    $process = proc_open(
        [PHP_BINARY, '-S', "127.0.0.1:$port", '-t', "$directory/public", "$directory/public/index.php"],
        [0 => ['pipe', 'r'], 1 => ['file', "$directory/server.log", 'a'], 2 => ['file', "$directory/server.log", 'a']],
        $pipes,
        $directory
    );

    if (!is_resource($process)) {
        throw new RuntimeException('Could not start the PHP server');
    }

    fclose($pipes[0]);
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $socket = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.05);
        if (is_resource($socket)) {
            fclose($socket);
            return $process;
        }

        if (!proc_get_status($process)['running']) {
            break;
        }

        usleep(20000);
    }

    proc_terminate($process);
    proc_close($process);
    throw new RuntimeException('PHP server did not start: ' . file_get_contents("$directory/server.log"));
}

function removeDirectory(string $directory): void
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }

    rmdir($directory);
}

$root = dirname(__DIR__);
$directory = sys_get_temp_dir() . '/php-notes-api-' . bin2hex(random_bytes(8));
$server = null;
$exitCode = 0;
$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }

    $checks++;
};

try {
    mkdir($directory);
    foreach (['public', 'src', 'data', 'database'] as $folder) {
        mkdir("$directory/$folder");
    }
    copy("$root/public/index.php", "$directory/public/index.php");
    copy("$root/database/schema.sql", "$directory/database/schema.sql");
    foreach (glob("$root/src/*.php") as $file) {
        copy($file, "$directory/src/" . basename($file));
    }

    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
    if ($socket === false) {
        throw new RuntimeException("Could not select a test port: $errorMessage");
    }
    $address = stream_socket_get_name($socket, false);
    $port = (int) substr(strrchr($address, ':'), 1);
    fclose($socket);

    $server = startServer($directory, $port);
    $url = "http://127.0.0.1:$port";

    $response = requestApi($url, '/api/ping');
    $check($response['status'] === 200 && $response['body'] === ['status' => 'ok'], 'Ping must return 200 and ok');

    $response = requestApi($url, '/api/notes');
    $check($response['status'] === 200 && $response['body'] === ['data' => []], 'A new database must have no notes');

    $title = "Заметка с 'кавычками'";
    $text = "Текст с Unicode и SQL-фрагментом: '); DROP TABLE notes; --";
    $payload = json_encode(['title' => " $title ", 'text' => " $text "], JSON_THROW_ON_ERROR);
    $response = requestApi($url, '/api/notes', 'POST', $payload);
    $check($response['status'] === 201, 'Creating a note must return 201');
    $first = $response['body']['data'];
    $check(is_int($first['id']) && $first['id'] > 0, 'Created note must have a positive integer ID');
    $check($first['title'] === $title && $first['text'] === $text, 'Strings must be trimmed and stored literally');
    $check(
        is_string($first['created_at']) && preg_match('/^\d{4}-\d{2}-\d{2}T/', $first['created_at']) === 1,
        'Created note must have a timestamp'
    );

    $response = requestApi($url, '/api/notes/' . $first['id']);
    $check($response['status'] === 200 && $response['body']['data'] === $first, 'Reading a note must return stored data');

    $response = requestApi($url, '/api/notes', 'POST', '{"title":"Вторая заметка","text":"Второй текст"}');
    $check($response['status'] === 201, 'Creating another note must succeed');
    $secondId = $response['body']['data']['id'];
    $response = requestApi($url, '/api/notes');
    $check(
        $response['status'] === 200 && array_column($response['body']['data'], 'id') === [$secondId, $first['id']],
        'The list must contain saved notes in descending ID order'
    );

    $response = requestApi($url, '/api/notes/' . ($secondId + 1));
    $check($response['status'] === 404 && isset($response['body']['error']), 'An absent note must return JSON and 404');

    $invalidFields = [
        ['{"title":"","text":"Текст"}', ['title']],
        ['{"title":"   ","text":"Текст"}', ['title']],
        ['{"title":"Заголовок","text":""}', ['text']],
        ['{"title":[],"text":"Текст"}', ['title']],
        ['{"title":true,"text":"Текст"}', ['title']],
        ['{"title":"Заголовок","text":null}', ['text']],
        ['{}', ['title', 'text']],
    ];
    foreach ($invalidFields as [$body, $fields]) {
        $response = requestApi($url, '/api/notes', 'POST', $body);
        $check($response['status'] === 422, "Invalid fields must return 422: $body");
        $check(array_keys($response['body']['errors']) === $fields, "Only invalid fields must have errors: $body");
    }

    foreach (['', '{"title":', '[]', 'null', '"string"', '42'] as $body) {
        $response = requestApi($url, '/api/notes', 'POST', $body);
        $check(
            $response['status'] === 422 && isset($response['body']['errors']['body']),
            "Invalid JSON body must return 422: $body"
        );
    }

    $response = requestApi($url, '/api/notes');
    $check(count($response['body']['data']) === 2, 'Invalid requests must not create notes');
    $response = requestApi($url, '/api/notes', 'PUT');
    $check($response['status'] === 405, 'An unsupported method must return 405');
    $check(in_array('Allow: GET, POST', $response['headers'], true), 'A 405 response must list allowed methods');
    $response = requestApi($url, '/api/unknown');
    $check($response['status'] === 404, 'An unknown route must return 404');

    proc_terminate($server);
    proc_close($server);
    $server = null;
    $server = startServer($directory, $port);
    $response = requestApi($url, '/api/notes/' . $first['id']);
    $check($response['status'] === 200 && $response['body']['data'] === $first, 'Notes must survive a server restart');

    echo "OK: $checks API checks passed\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }
    if (is_dir($directory)) {
        removeDirectory($directory);
    }
}

exit($exitCode);
