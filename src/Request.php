<?php

declare(strict_types=1);

/**
 * Чтение и разбор данных текущего HTTP-запроса.
 */
final class Request
{
    /**
     * Читает JSON-тело текущего HTTP-запроса.
     *
     * При пустом или некорректном JSON возвращает HTTP 422.
     *
     * @return array<string, mixed> Декодированный JSON-объект запроса.
     */
    public static function json(): array
    {
        $rawBody = file_get_contents('php://input');

        if ($rawBody === false || trim($rawBody) === '') {
            Response::json(['errors' => ['body' => 'Request body must be valid JSON.']], 422);
        }

        try {
            $data = json_decode($rawBody, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            Response::json(['errors' => ['body' => 'Request body must be valid JSON.']], 422);
        }

        if (!is_array($data)) {
            Response::json(['errors' => ['body' => 'JSON body must be an object.']], 422);
        }

        return $data;
    }
}
