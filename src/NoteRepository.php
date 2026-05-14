<?php

declare(strict_types=1);

/**
 * Доступ к данным таблицы notes через PDO.
 */
final class NoteRepository
{
    /**
     * Принимает подключение к БД для выполнения SQL-запросов.
     *
     * @param PDO $pdo Подключение к SQLite, созданное в Database::connect().
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Возвращает список всех заметок.
     *
     * @return array<int, array<string, mixed>> Список заметок из таблицы notes.
     */
    public function all(): array
    {
        // TODO: выполнить SELECT id, title, text, created_at FROM notes ORDER BY id DESC.
        return [];
    }

    /**
     * Создаёт новую заметку в БД.
     *
     * @param string $title Заголовок заметки, уже проверенный контроллером.
     * @param string $text Текст заметки, уже проверенный контроллером.
     *
     * @return array<string, mixed> Созданная заметка.
     */
    public function create(string $title, string $text): array
    {
        // TODO: отправить запрос на создании заметки и вернуть заметку.
        return [
            'id' => 0,
            'title' => $title,
            'text' => $text,
            'created_at' => date(DATE_ATOM),
        ];
    }

    /**
     * Ищет одну заметку по id.
     *
     * @param int $id Идентификатор заметки из URL.
     *
     * @return array<string, mixed>|null Массив с заметкой или null, если записи нет.
     */
    public function find(int $id): ?array
    {
        // TODO: выполнить SELECT по id и вернуть заметку или null.
        return null;
    }
}
