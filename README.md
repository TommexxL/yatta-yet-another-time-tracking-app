# YATTA

YATTA is a Laravel 13 time tracking and leave management application. It uses Fortify for regular user authentication, Filament for the admin panel, and Spatie roles for access control.

## Requirements

- PHP 8.3 or higher
- Composer
- Node.js and npm
- MySQL or SQLite

## Installation

Clone the repository and enter the project folder:

```bash
git clone <repository-url>
cd backend-eindproef
```

Install PHP dependencies:

```bash
composer install
```

Create the environment file:

```powershell
Copy-Item .env.example .env
```

On macOS/Linux:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Install frontend dependencies:

```bash
npm install
```

## Database Setup

The default `.env.example` is configured for MySQL. Create a database, then update these values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yatta
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed demo data:

```bash
php artisan migrate:fresh --seed
```

Alternative SQLite setup:

```powershell
New-Item -ItemType File database/database.sqlite
```

Then set this in `.env`:

```env
DB_CONNECTION=sqlite
```

After changing `.env`, run:

```bash
php artisan config:clear
php artisan migrate:fresh --seed
```

## Running the Application

For normal development, run the Laravel server:

```bash
php artisan serve
```

In another terminal, run Vite:

```bash
npm run dev
```

Open the app at:

```text
http://127.0.0.1:8000
```

Admin panel:

```text
http://127.0.0.1:8000/admin
```

You can also run the combined development command:

```bash
composer run dev
```

That starts the Laravel server, queue listener, log tail, and Vite together.

For a production-style frontend build:

```bash
npm run build
```

## Testing

Run the test suite:

```bash
php artisan test
```

Or use the Composer script:

```bash
composer test
```

## Demo Accounts

After running `php artisan migrate:fresh --seed`, these demo users are available. All seeded users use this password:

```text
password
```

| Role | Email | Where to log in | Notes |
| --- | --- | --- | --- |
| Admin | `admin@yatta.test` | `/admin` | Admin users are blocked from the public login and must use the Filament admin panel. |
| Manager | `manager@yatta.test` | `/login` | Can access profile, schedule, manager overview, schedule management, and approvals. |
| Manager | `teamlead@yatta.test` | `/login` | Second manager account for testing manager workflows. |
| Employee | `employee@yatta.test` | `/login` | Regular employee account for clocking, schedule viewing, corrections, vacation, and sick leave requests. |

The seeder also creates extra employee accounts with generated email addresses for richer demo data, but `employee@yatta.test` is the stable regular user account to test the employee flow.

## Main Test Flows

Admin flow:

1. Go to `/admin`.
2. Log in as `admin@yatta.test`.
3. Manage users, companies, schedules, leave requests, sick leaves, and time entries through Filament.

Manager flow:

1. Go to `/login`.
2. Log in as `manager@yatta.test`.
3. Open Profile.
4. Use Manage to approve time corrections, vacation requests, and sick leave.
5. Use Manage Schedules to add, edit, or remove schedules.
6. Assign schedules to employees from the manager overview.

Employee flow:

1. Log in as `employee@yatta.test`.
2. Clock in and clock out from the profile page.
3. Open Schedule.
4. Request a time correction for a schedule day.
5. Request vacation or sick leave from a schedule day.
6. After manager approval, approved vacation/sick leave appears in the schedule instead of normal work hours.

## Useful Commands

Clear cached config:

```bash
php artisan config:clear
```

Reset and reseed the database:

```bash
php artisan migrate:fresh --seed
```

Run the queue worker manually:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

View routes:

```bash
php artisan route:list
```
