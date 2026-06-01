# Events API

Laravel 12 API for a paginated list of events with category and date filters.

## Requirements

- PHP 8.2+
- Composer
- SQLite enabled for PHP

## Setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File -Force
php artisan migrate:fresh --seed
php artisan serve
```

For SQLite, keep `.env` configured with:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

## API

### Get events

```http
GET /api/events
```

Query parameters:

- `category_ids`: category ids as an array: `category_ids[]=1&category_ids[]=2`
- `date_from`: start date in `YYYY-MM-DD`
- `date_to`: end date in `YYYY-MM-DD`
- `per_page`: items per page, from `1` to `100`
- `page`: page number

Examples:

```http
GET /api/events
GET /api/events?category_ids[]=1&category_ids[]=2
GET /api/events?date_from=2026-06-01&date_to=2026-07-31
GET /api/events?category_ids[]=2&category_ids[]=3&date_from=2026-06-01&date_to=2026-08-31&per_page=2&page=1
```

The response uses Laravel API resource pagination and includes `data`, `links`, and `meta`.

The request flow is intentionally simple: `EventController` validates input, `EventService` owns the use case, and `EventRepository` builds the Eloquent query.

## Seed Data

The database seeder creates fixed categories and sample events:

- Conference
- Workshop
- Meetup
- Webinar
- Hackathon

Run seeders with:

```powershell
php artisan migrate:fresh --seed
```

## Postman

Import this collection into Postman:

```text
docs/postman/events-api.postman_collection.json
```

The collection includes requests for all events, category filters, date filters, and paginated combined filters.

## Tests

```powershell
php artisan test
```

## Publish To GitHub

Create a public GitHub repository, then run:

```powershell
git init
git add .
git commit -m "Add events API with filtering and seeding"
git branch -M main
git remote add origin <public-github-repo-url>
git push -u origin main
```
