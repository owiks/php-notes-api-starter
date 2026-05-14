<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Request.php';
require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/NoteRepository.php';
require_once __DIR__ . '/../src/NoteController.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';

if ($method === 'GET' && $path === '/') {
    Response::json([
        'name' => 'Native PHP Notes API Starter',
        'message' => 'Open /api/ping or start implementing /api/notes',
    ]);
}

if ($method === 'GET' && $path === '/api/ping') {
    Response::json(['status' => 'ok']);
}

$pdo = Database::connect(__DIR__ . '/../data/database.sqlite');
$notes = new NoteRepository($pdo);
$controller = new NoteController($notes);

if ($path === '/api/notes') {
    if ($method === 'GET') {
        $controller->index();
    }

    if ($method === 'POST') {
        $controller->store();
    }

    Response::methodNotAllowed(['GET', 'POST']);
}

if ($method === 'GET' && preg_match('#^/api/notes/(\d+)$#', $path, $matches)) {
    $controller->show((int) $matches[1]);
}

Response::json(['error' => 'Route not found'], 404);
