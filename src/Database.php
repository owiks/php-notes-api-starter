<?php

declare(strict_types=1);

/**
 * Подключение к SQLite и инициализация схемы приложения.
 */
final class Database
{
    /**
     * Создаёт PDO-подключение к SQLite-файлу.
     *
     * @param string $path Абсолютный или относительный путь к SQLite-файлу.
     *
     * @return PDO Подключение с режимом ошибок через исключения.
     */
    public static function connect(string $path): PDO
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::createTables($pdo);

        return $pdo;
    }

    /**
     * Выполняет создание таблицы notes, если она ещё не существует.
     *
     * @param PDO $pdo Активное подключение к SQLite.
     */
    private static function createTables(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS notes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                text TEXT NOT NULL,
                created_at TEXT NOT NULL
            )'
        );
    }
}
