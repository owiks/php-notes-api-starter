<?php

declare(strict_types=1);

/**
 * HTTP-контроллер для эндпоинтов заметок.
 */
final class NoteController
{
    /**
     * Принимает репозиторий заметок.
     *
     * @param NoteRepository $notes Слой доступа к таблице notes.
     */
    public function __construct(private NoteRepository $notes)
    {
    }

    /**
     * Обрабатывает `GET /api/notes`.
     */
    public function index(): void
    {
        Response::json(['data' => $this->notes->all()]);
    }

    /**
     * Обрабатывает `POST /api/notes`.
     */
    public function store(): void
    {
        $input = Request::json();

        $title = $input['title'] ?? null;
        $text = $input['text'] ?? null;
        $errors = [];

        if (!is_string($title) || trim($title) === '') {
            $errors['title'] = 'title must be a non-empty string';
        }

        if (!is_string($text) || trim($text) === '') {
            $errors['text'] = 'text must be a non-empty string';
        }

        if ($errors !== []) {
            Response::json(['errors' => $errors], 422);
        }

        $note = $this->notes->create(trim($title), trim($text));
        Response::json(['data' => $note], 201);
    }

    /**
     * Обрабатывает `GET /api/notes/{id}`.
     *
     * @param int $id Идентификатор заметки, извлечённый из URL.
     */
    public function show(int $id): void
    {
        $note = $this->notes->find($id);

        if ($note === null) {
            Response::json(['error' => 'Note not found'], 404);
        }

        Response::json(['data' => $note]);
    }
}
