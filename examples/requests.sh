#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8000}"

echo "GET /api/ping"
curl -i "$BASE_URL/api/ping"

echo
echo "POST /api/notes"
NOTE_RESPONSE=$(curl --fail --silent --show-error -X POST "$BASE_URL/api/notes" \
  -H "Content-Type: application/json" \
  -d '{"title":"Первая заметка","text":"Текст заметки"}')
printf '%s\n' "$NOTE_RESPONSE"
NOTE_ID=$(printf '%s' "$NOTE_RESPONSE" | php -r '
    $response = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
    echo $response["data"]["id"];
')

echo
echo "GET /api/notes"
curl -i "$BASE_URL/api/notes"

echo
echo "GET /api/notes/$NOTE_ID"
curl --fail --show-error -i "$BASE_URL/api/notes/$NOTE_ID"
