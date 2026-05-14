# Native PHP Notes API Starter

Скелет backend-проекта на нативном PHP без фреймворка.

## Что уже готово

- `GET /api/ping`
- встроенный PHP-сервер
- подключение к SQLite через PDO
- автоматическое создание таблицы `notes`
- helper для JSON-ответов
- helper для чтения JSON-запроса
- заготовки controller/repository

## Что нужно дописать

- `POST /api/notes` — создать заметку
- `GET /api/notes` — получить список заметок
- `GET /api/notes/{id}` — получить одну заметку по id

Подробный порядок выполнения лежит в [TASK.md](TASK.md).

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
