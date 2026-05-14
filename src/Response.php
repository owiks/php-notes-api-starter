<?php

declare(strict_types=1);

/**
 * Формирование HTTP-ответов в формате JSON.
 */
final class Response
{
    /**
     * Отправляет JSON-ответ клиенту и завершает выполнение скрипта.
     *
     * @param array<string, mixed> $payload Данные, которые будут закодированы в JSON.
     * @param int $status HTTP-статус ответа.
     *
     * @return never
     */
    public static function json(array $payload, int $status = 200): never
    {
        $reasons = [
            200 => 'OK',
            201 => 'Created',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            501 => 'Not Implemented',
        ];

        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        $reason = $reasons[$status] ?? 'OK';

        header("$protocol $status $reason", true, $status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
        exit;
    }

    /**
     * Возвращает HTTP 501 для ещё не реализованного обработчика.
     *
     * @param string $task Название незавершённого обработчика.
     *
     * @return never
     */
    public static function todo(string $task): never
    {
        self::json([
            'todo' => $task,
            'hint' => 'Open src/NoteController.php and src/NoteRepository.php',
        ], 501);
    }

    /**
     * Возвращает ошибку, когда маршрут существует, но HTTP-метод не поддерживается.
     *
     * @param array<int, string> $allowedMethods Список разрешённых HTTP-методов.
     *
     * @return never
     */
    public static function methodNotAllowed(array $allowedMethods): never
    {
        header('Allow: ' . implode(', ', $allowedMethods));
        self::json(['error' => 'Method not allowed'], 405);
    }
}
