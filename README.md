# Native PHP Notes API Starter

Учебный API заметок на нативном PHP без фреймворка. Заметки сохраняются в SQLite через PDO.

## Реализованные маршруты

- `GET /api/ping` — проверка доступности, `200`.
- `POST /api/notes` — создание заметки, `201`.
- `GET /api/notes` — список заметок от новых к старым, `200`.
- `GET /api/notes/{id}` — одна заметка, `200`, либо `404`, если её нет.

В POST передаются строки `title` и `text`. Пробелы по краям удаляются. Отсутствующие,
пустые поля, неверные типы значений и некорректный JSON возвращают `422`.
Тело запроса должно быть JSON-объектом.

Успешные ответы заметок содержат поле `data`. Заметка имеет поля `id`, `title`, `text`,
`created_at`. При отсутствии заметок список возвращается как `{"data": []}`.
Ошибки полей возвращаются в `errors`, ошибка отсутствующей заметки — в `error`.

Исходное учебное задание сохранено в [TASK.md](TASK.md).

## Требования

- PHP 8.1 или новее
- Расширения `pdo_sqlite` и `sqlite3`

## Установка на Ubuntu

Обновить список пакетов:

```bash
sudo apt update
```

Установить PHP для командной строки, SQLite-расширения для PHP и SQLite CLI:

```bash
sudo apt install -y php-cli php-sqlite3 sqlite3
```

Пакет `php-sqlite3` ставит SQLite-модуль для PHP. После установки в `php -m` должны быть доступны `pdo_sqlite` и `sqlite3`.

Проверка:

```bash
php -v
php -m | grep -E 'pdo_sqlite|sqlite3'
sqlite3 --version
```

Дополнительная проверка PDO SQLite:

```bash
php -r "new PDO('sqlite::memory:'); echo 'pdo_sqlite ok' . PHP_EOL;"
```

Если используется конкретная версия PHP, можно поставить версионный пакет. Например, для PHP 8.3:

```bash
sudo apt install -y php8.3-cli php8.3-sqlite3 sqlite3
```

## Запуск локально

Из папки проекта:

```bash
php -S localhost:8000 -t public public/index.php
```

Проверка:

```bash
curl http://localhost:8000/api/ping
```

Если порт `8000` занят:

```bash
php -S localhost:8080 -t public public/index.php
```

## Проверка API

Создать заметку:

```bash
curl -i -X POST http://localhost:8000/api/notes \
  -H 'Content-Type: application/json' \
  -d '{"title":"Первая заметка","text":"Текст заметки"}'
```

Получить список и одну заметку. Вместо `1` подставить `id` из ответа создания:

```bash
curl -i http://localhost:8000/api/notes
curl -i http://localhost:8000/api/notes/1
```

Последовательность запросов с ID созданной заметки:

```bash
bash examples/requests.sh
```

Для другого порта:

```bash
BASE_URL=http://localhost:8080 bash examples/requests.sh
```

## Автоматические проверки

```bash
php tests/api.php
```

Проверки запускают временную копию приложения со встроенным PHP-сервером
на свободном локальном порту и отдельной SQLite-базой. Основная база проекта
не меняется. Проверяются создание, список, чтение по ID, сохранение после
перезапуска, а также ответы `404`, `405` и `422`. После завершения временные файлы удаляются.

## Запуск через Docker

Вариант с Docker не требует установки PHP на хостовую систему. Нужны только Docker и Docker Compose.

Образ собирается на базе официального `php:8.4-cli-bookworm`. В нём уже доступны PHP-расширения `pdo_sqlite` и `sqlite3`; в `Dockerfile` дополнительно ставится SQLite CLI.

Собрать и запустить контейнер:

```bash
docker compose up --build
```

Проверить API:

```bash
curl http://localhost:8000/api/ping
```

Остановить контейнер:

```bash
docker compose down
```

В Docker-профиле проект монтируется в контейнер как volume, поэтому изменения в PHP-файлах видны без пересборки образа. Пересборка нужна только при изменении `Dockerfile`.

База SQLite создаётся в локальной папке `data/database.sqlite`. Если на Linux появится ошибка прав на запись, выполнить:

```bash
chmod -R u+rwX data
```

Файл `data/database.sqlite` создаётся автоматически при первом обращении к маршрутам заметок.
