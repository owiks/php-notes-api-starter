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
        // TODO: вернуть список заметок из NoteRepository.
        // Response::json(['data' => $this->notes->all()]);
        Response::todo('Implement GET /api/notes');
    }

    /**
     * Обрабатывает `POST /api/notes`.
     */
    public function store(): void
    {
        $input = Request::json();

        $title = trim((string) ($input['title'] ?? ''));
        $text = trim((string) ($input['text'] ?? ''));

        if ($title === '' || $text === '') {
            Response::json([
                'errors' => [
                    'title' => 'title is required',
                    'text' => 'text is required',
                ],
            ], 422);
        }

        // TODO: создать заметку через NoteRepository.
        Response::todo('Implement POST /api/notes');
    }

    /**
     * Обрабатывает `GET /api/notes/{id}`.
     *
     * @param int $id Идентификатор заметки, извлечённый из URL.
     */
    public function show(int $id): void
    {
        // TODO: найти заметку через NoteRepository или вернуть 404.

        // if ($note === null) {
        //     Response::json(['error' => 'Note not found'], 404);
        // }
        //
        // Response::json(['data' => $note]);
        Response::todo('Implement GET /api/notes/{id}');
    }
}
