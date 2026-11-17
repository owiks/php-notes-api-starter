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
        $statement = $this->pdo->query(
            'SELECT id, title, text, created_at FROM notes ORDER BY id DESC'
        );

        return $statement->fetchAll();
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
        $createdAt = date(DATE_ATOM);
        $statement = $this->pdo->prepare(
            'INSERT INTO notes (title, text, created_at) VALUES (:title, :text, :created_at)'
        );
        $statement->execute([
            'title' => $title,
            'text' => $text,
            'created_at' => $createdAt,
        ]);

        return [
            'id' => (int) $this->pdo->lastInsertId(),
            'title' => $title,
            'text' => $text,
            'created_at' => $createdAt,
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
        $statement = $this->pdo->prepare(
            'SELECT id, title, text, created_at FROM notes WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $note = $statement->fetch();

        return $note === false ? null : $note;
    }
}
