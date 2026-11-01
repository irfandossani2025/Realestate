# Plesk deployment runbook

Target: `realestate.techittechnologies.com`

## Required host capabilities

- PHP 8.3 or newer with PDO MySQL, mbstring, OpenSSL, tokenizer, XML, cURL, fileinfo, and ZIP
- Composer 2
- MariaDB with InnoDB and `utf8mb4`
- HTTPS certificate for the subdomain
- Plesk scheduled tasks

Node.js is not required. The ERP uses server-rendered Laravel views and does not run a frontend build during deployment, so the host's Node 17.9 limit does not affect production.

MariaDB 10.1 and PostgreSQL 10 are end-of-life products. The current schema avoids native JSON columns for MariaDB 10.1 compatibility, but the database should be upgraded when the host permits it for security and long-term support.

## Plesk configuration

1. Clone `irfandossani2025/Realestate` from the `main` branch.
2. Set the domain document root to the repository's `public` directory. Never expose the repository root.
3. Create a MariaDB database and database user in Plesk.
4. Copy `.env.example` to `.env` outside Git tracking and enter the database credentials. Set `APP_ENV=production`, `APP_DEBUG=false`, and the production URL.
5. Generate the application key once with `php artisan key:generate`.
6. Keep `storage` and `bootstrap/cache` writable by the subscription's PHP user.

## Deployment commands

Run from the repository root:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Create the first administrator through a hidden password prompt:

```bash
php artisan erp:create-admin admin@alramzinternational.com --name="ERP Administrator"
```

Do not place passwords, mailbox credentials, or API keys in GitHub or deployment commands.

## Scheduled task

Configure this Plesk task to run every minute:

```bash
php /absolute/path/to/repository/artisan schedule:run
```

Laravel runs the renewal check daily at 08:00 Asia/Muscat time.

## Post-deployment checks

```bash
php artisan about --only=environment,cache,drivers
php artisan migrate:status
php artisan route:list
```

Then verify HTTPS login, Arabic RTL switching, administrator access to all three companies, private document storage, and that `/api/v1/properties` contains only approved published listings.
