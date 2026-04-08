<laravel-boost-guidelines>
=== .ai/00-forbidden rules ===

- Do not put business logic inside Controllers or Blade templates.
- Avoid Fat Controllers or massive "God" Services.
- Do not abuse static helpers for business logic.
- Do not use raw `request()` usage; use Form Requests and `$request->validated()`.
- Do not return raw Eloquent models from APIs; use API Resources.
- Avoid unpaginated large dataset queries.
- Do not allow unvalidated input or mass assignment vulnerabilities.
- Avoid global state mutation or hidden side effects.
- Do not use `env()` outside of configuration files.
- Never commit debug code (`dd()`, `dump()`, `ray()`, `console.log()`).
- Remove commented-out production code and unused imports.
- Never trust external input without validation and authorization.
- Avoid excessive use of Facades within core domain logic.
- Do not use outdated helper patterns or deprecated Laravel features.
- Avoid inconsistent or non-deterministic code generation.
- Generate strictly typed PHP with `declare(strict_types=1);`.
- Follow PSR-12 and Laravel idiomatic structures.
- Assume Rector, Larastan, and Pint are part of the pipeline.
- Produce testable, modular architecture by default.
- Ensure all code is statically analyzable and minimal.
- Do not hardcode route URLs in frontend code; use Wayfinder (`@/actions/`, `@/routes/`).
- Do not use Blade views for application pages; use Inertia + Vue components.
- Do not manipulate `$page.props` directly outside of Inertia composables.
- Do not use `<a href>` for in-app navigation; use Inertia `<Link>`.
- Do not use raw `fetch` or `axios` for Inertia-managed requests; use `useForm()` or `useHttp()`.

=== .ai/01-coding-standards rules ===

- Prioritize readability over cleverness and consistency over creativity.
- Use RESTful controllers only (index, store, show, update, destroy).
- Move complex logic from controllers to Actions or Services.
- Follow PSR-12 coding style.
- Limit line length to 120 characters.
- Use `snake_case` for database columns, `camelCase` for variables and methods, and `PascalCase` for classes.
- Prefix methods with a verb (e.g., `updatePassword`).
- Prefix boolean methods with `is` or `has` (e.g., `isActive`).
- Use FormRequests for validation, Services for business logic, and Models for persistence.
- Format API responses as `{"success": bool, "message": string, "data": {}}` with standard HTTP codes.
- Use API Resources for all API responses; never return Eloquent models directly.
- Use the `Storage` facade for file management and organize by entity (e.g., `users/{id}/avatar.jpg`).
- Keep controllers under 30 lines per method and 8 methods per class.
- Access validated data only via `$request->validated()`; never trust `request()` directly.
- Store secrets in `.env` only and access them via `config()`.
- Use Conventional Commits (`feat:`, `fix:`, `refactor:`, `test:`, `chore:`).
- Use Pest for all PHP tests; name tests with `it('does something')` sentence style.
- Use TypeScript in all frontend files; Vue components must use `<script setup lang="ts">`.
- Use `camelCase` for TypeScript variables and `PascalCase` for component names and interfaces.

=== .ai/02-architecture rules ===

- Use skinny controllers and fat services; controllers must remain thin and never perform heavy loops.
- Limit the controller role to validation, authorization, and delegation.
- Use the service layer for multi-model logic and 3rd-party integrations; keep services stateless.
- Use single-purpose Actions for atomic units of work (e.g., `CreateOrder`).
- Use idempotent Jobs for asynchronous or heavy tasks.
- Use Models as data containers; keep logic within Scopes, Accessors, or Mutators without side effects.
- Use strict PHP: include `declare(strict_types=1);`, and use mandatory typed properties and return types.
- Avoid mixed types, dynamic properties, and magic array structures.
- Use constructor injection instead of global helpers for dependency management.
- Use constructor property promotion and readonly properties where applicable.
- Use DTOs for passing immutable data across different layers.
- Use Policies for all resource authorization logic.
- Map backend folders as follows:
    - `app/Actions/`: Single-purpose executors.
    - `app/Services/`: Complex business logic.
    - `app/DTOs/`: Data transfer objects.
    - `app/Enums/`: Enumerations.
    - `app/Jobs/`: Background tasks.
    - `app/Models/`: Eloquent models.
    - `app/Policies/`: Authorization logic.
    - `app/Http/Requests/`: Form validation.
    - `app/Http/Resources/`: API resources.
- Map frontend folders as follows:
    - `resources/js/pages/`: Inertia page components (one per route).
    - `resources/js/components/`: Reusable Vue components.
    - `resources/js/composables/`: Vue composables for shared logic.
    - `resources/js/types/`: TypeScript interfaces and type definitions.
    - `resources/js/actions/`: Auto-generated Wayfinder controller actions (do not edit).
    - `resources/js/routes/`: Auto-generated Wayfinder named routes (do not edit).
- Keep Inertia page components thin; delegate data-fetching logic to composables and business logic to backend Actions.

=== .ai/03-database rules ===

- Use plural `snake_case` for tables and `snake_case` for columns.
- Use singular `model_id` for foreign keys.
- Order pivot table names alphabetically (e.g., `post_tag`).
- Ensure each migration has a single responsibility.
- Use `constrained()->cascadeOnDelete()` for foreign key constraints.
- Index all columns used in `where`, `order by`, or as foreign keys.
- Define `fillable` or `guarded` attributes in models to prevent mass assignment.
- Declare relationships explicitly in model classes.
- Use the `casts()` method for defining attribute types (e.g., bool, array, enum).
- Use Query Scopes for shared database filters.
- Always use eager loading (`with()` or `load()`) to prevent N+1 query problems.
- Use `paginate()` by default; avoid `all()` on large datasets.
- Use Database Transactions for multi-step write operations.
- Use `chunk()` or `lazy()` when processing large result sets.
- Use `fake()` in factories and create environment-specific seeders.
- Avoid raw SQL unless explicitly justified.

=== .ai/04-testing rules ===

- Test the behavior of the application rather than its implementation.
- Use Feature Tests for Controllers, HTTP requests, database interactions, and response flows.
- Use Unit Tests for Services and isolated pure logic without database or I/O access.
- Use Policy tests for all authorization logic.
- Write tests using Pest v4 syntax: `it('does something', function () { ... })`.
- Follow the AAA (Arrange, Act, Assert) pattern in every test using Pest's `expect()` API.
- Use Pest datasets for data-driven tests instead of duplicating test cases.
- Use `beforeEach()` for shared setup instead of duplicating arrange steps.
- Use Fakes for external services (e.g., `Bus::fake()`, `Mail::fake()`, `Http::fake()`).
- Use Factories for all test data; do not seed the database manually in tests.
- Use standard assertions: `->assertStatus()`, `->assertInertia()`, `->assertDatabaseHas()`.
- Use `assertInertia()` to verify Inertia responses return the correct component and props.
- Ensure `pint`, `larastan`, and `rector` pass before finishing.
- Maintain a minimum of 70% test coverage.

=== .ai/05-security rules ===

- Use Sanctum for SPA authentication (Fortify handles the backend routes).
- Use Blade `{{ }}` for output escaping in any Blade files; avoid `{!! !!}` unless data is pre-sanitized.
- Use `@csrf` in all HTML forms.
- Never log sensitive information such as passwords, tokens, or personal identifiable information.
- Run `composer audit` regularly to check for package vulnerabilities.
- Use Policies for all resource authorization; never inline authorization checks in controllers.
- Validate and authorize before any database write — never rely on frontend-only validation.
- Use HTTPS in all environments; never expose credentials in URLs or logs.

=== .ai/06-performance rules ===

- Cache configurations, routes, and views in production environments.
- Use `Cache::remember()` for frequently queried data.
- Select only the necessary columns in database queries; avoid `select(*)`.
- Build frontend assets using `npm run build` and lazy-load images and JavaScript.
- Use Laravel Debugbar locally to identify slow queries and memory issues.
- Use Inertia deferred props (`Inertia::defer()`) for non-critical or slow data to keep initial page load fast.
- Use Inertia `prefetch` on `<Link>` components for predictable navigation to pre-warm data.
- Tree-shake Wayfinder imports — only import the specific action functions you use, not entire modules.
- Avoid returning large prop payloads from Inertia responses; paginate or defer where possible.

=== .ai/07-frontend rules ===

- Always use `<script setup lang="ts">` in Vue single-file components.
- Vue components must have a single root element.
- Keep Inertia page components thin — data fetching belongs in composables, business logic in backend Actions.
- Use Wayfinder for all route/action references; never hardcode URLs.
  ```ts
  import { show } from '@/actions/UserController'
  // use show.url({ user: 1 }) or show({ user: 1 }) with useForm/useHttp
  ```
- Use `useForm()` for all form submissions to get automatic progress tracking and error handling.
- Use `useHttp()` for standalone XHR requests that are not tied to a full form.
- Use Inertia `<Link>` for all in-app navigation; never use `<a href>` for internal routes.
- Use `setLayoutProps` to pass data to the shared layout (title, breadcrumbs, etc.).
- Use Inertia `Inertia::defer()` on the backend for non-critical props; pair with a skeleton loader on the frontend.
- Use `prefetch` on `<Link>` for high-probability navigation targets to reduce perceived latency.
- Use optimistic updates via Inertia's `optimistic` option for instant UI feedback on mutations.
- Follow Tailwind v4 utility-first conventions; avoid writing custom CSS unless there is no utility equivalent.
- Define shared TypeScript types in `resources/js/types/`; import them consistently rather than using inline type literals.
- Use ESLint and Prettier for frontend code quality; run `npm run lint:check` and `npm run format:check` before committing.
- Run `php artisan wayfinder:generate` (or the Vite plugin equivalent) after any controller or route change to keep generated types in sync.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- @inertiajs/vue3 (INERTIA_VUE) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `fortify-development` — ACTIVATE when the user works on authentication in Laravel. This includes login, registration, password reset, email verification, two-factor authentication (2FA/TOTP/QR codes/recovery codes), profile updates, password confirmation, or any auth-related routes and controllers. Activate when the user mentions Fortify, auth, authentication, login, register, signup, forgot password, verify email, 2FA, or references app/Actions/Fortify/, CreateNewUser, UpdateUserProfileInformation, FortifyServiceProvider, config/fortify.php, or auth guards. Fortify is the frontend-agnostic authentication backend for Laravel that registers all auth routes and controllers. Also activate when building SPA or headless authentication, customizing login redirects, overriding response contracts like LoginResponse, or configuring login throttling. Do NOT activate for Laravel Passport (OAuth2 API tokens), Socialite (OAuth social login), or non-auth Laravel features.
- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `wayfinder-development` — Use this skill for Laravel Wayfinder which auto-generates typed functions for Laravel controllers and routes. ALWAYS use this skill when frontend code needs to call backend routes or controller actions. Trigger when: connecting any React/Vue/Svelte/Inertia frontend to Laravel controllers, routes, building end-to-end features with both frontend and backend, wiring up forms or links to backend endpoints, fixing route-related TypeScript errors, importing from @/actions or @/routes, or running wayfinder:generate. Use Wayfinder route functions instead of hardcoded URLs. Covers: wayfinder() vite plugin, .url()/.get()/.post()/.form(), query params, route model binding, tree-shaking. Do not use for backend-only task
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `inertia-vue-development` — Develops Inertia.js v3 Vue client-side applications. Activates when creating Vue pages, forms, or navigation; using <Link>, <Form>, useForm, useHttp, setLayoutProps, or router; working with deferred props, prefetching, optimistic updates, instant visits, or polling; or when user mentions Vue with Inertia, Vue pages, Vue forms, or Vue navigation.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
