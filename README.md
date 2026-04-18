# Student Absence Report System Backend (Laravel)

This backend provides the API for admin, teacher, and student workflows:

- Sanctum authentication
- Role-protected routes under `/api/admin`, `/api/teacher`, `/api/student`
- Attendance, absence reports, announcements, and CSV import/export
- Notifications via mail and SMS channels (queued)

## Requirements

- PHP 8.2+
- Composer
- SQLite/MySQL/PostgreSQL

## Local setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

API default local URL: `http://127.0.0.1:8000`

## Environment variables

See `../CONFIGURATION.md` for full details. Most important:

- `APP_URL`
- `FRONTEND_URL` (used in student invite links)
- `CORS_ALLOWED_ORIGINS`
- `QUEUE_CONNECTION=database`
- `FRONTEND_API_KEY` (optional extra request gate; not a browser-secret)
- `TWILIO_*` for real SMS
- `MAIL_*` for real email

## Queue worker (required in production)

Notifications are queued. Run at least one worker:

```bash
php artisan queue:work --tries=3 --timeout=120
```

If using Supervisor/systemd, keep a long-running worker process alive.

## Tests and checks

```bash
php artisan test
```

## API notes

- Health endpoint: `GET /api/health`
- Optional API key gate: set `FRONTEND_API_KEY` and send matching `X-API-Key`
- Auth flow: `POST /api/auth/login` then `Authorization: Bearer <token>`

## Deployment checklist

1. Set production `.env` values (`APP_ENV=production`, `APP_DEBUG=false`)
2. Restrict `CORS_ALLOWED_ORIGINS`
3. Configure mail/SMS providers
4. Run migrations
5. Start queue worker(s)
6. Verify `GET /api/health`
