#!/usr/bin/env bash

BASE_URL="${BASE_URL:-http://localhost:8000}"

echo "GET /api/ping"
curl -i "$BASE_URL/api/ping"

echo
echo "POST /api/notes"
curl -i -X POST "$BASE_URL/api/notes" \
  -H "Content-Type: application/json" \
  -d '{"title":"Первая заметка","text":"Текст заметки"}'

echo
echo "GET /api/notes"
curl -i "$BASE_URL/api/notes"

echo
echo "GET /api/notes/1"
curl -i "$BASE_URL/api/notes/1"
