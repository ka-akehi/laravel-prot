# Repository Guidelines

## Project Structure & Module Organization
Application code lives under `app/`, following Laravel's default namespaces for HTTP controllers, jobs, and domain services. Route definitions sit in `routes/` (`web.php` for browser flows, `api.php` for JWT-backed endpoints). Database migrations, factories, and seeders are in `database/`; large seed datasets (`countries_*.csv`) feed custom seeders. Frontend views and assets are in `resources/`, compiled through Vite into `public/`. Container and tooling assets (`docker/`, `Makefile`) support local orchestration, while tests are arranged under `tests/Feature` and `tests/Unit`.

## Build, Test, and Development Commands
- `composer install && npm install`: install PHP vendors and front-end toolchain.
- `make build-up`: build the base Docker image, then start the full stack.
- `make up` / `make down`: start or stop the stack without rebuilding.
- `make migrate` or `make migrate-fresh`: run schema changes inside the running container.
- `composer run dev`: launch the full dev loop (Laravel server, queue listener, log tail, Vite).
- `composer test` or `php artisan test`: execute the automated test suite with configuration reset.

## Coding Style & Naming Conventions
Follow PSR-12 spacing with four-space indentation in PHP; run `./vendor/bin/pint` before pushing. Name controllers and jobs with PascalCase suffixes (`CountriesController`), models singular (`Country`), and database tables in snake_case plural. Frontend JavaScript should remain ES modules, camelCase for variables, kebab-case for Vue or Blade component filenames. Use `php artisan make:*` generators to keep namespaces aligned.

## Testing Guidelines
Prefer feature tests for HTTP flows and unit tests for isolated services. Mirror class names in test classes (`CountryServiceTest`). Seed data per test using factories or the CSV-backed seeders to avoid shared state. Aim for coverage of new branches and queue jobs. Run `php artisan test --coverage` locally when introducing cross-module changes.

## Commit & Pull Request Guidelines
Recent history uses concise, lowercase verbs (`add`, `fix`) as the lead word; keep summaries under 50 characters and append context when helpful (`fix: adjust country seeder`). Reference issues with `#ID` when applicable. Pull requests should describe scope, validation steps (commands run, screenshots for UI), and note schema or environment changes so reviewers can recreate them.

## Environment & Configuration Tips
Copy `.env.example` to `.env`, then set queue, Redis, and JWT secrets before running `make up`. Use the Docker targets for MySQL access (`make mysql-root` or `make mysql-laravel`) instead of direct host connections. Persist schema exports with `make dump-ddl` whenever migrations change the structure.
