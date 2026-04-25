# Laravel Multi-Auth Starter

> Laravel 13 · Vue 3 · Inertia.js v3 · Tailwind CSS v4 · TypeScript

![PHP](https://img.shields.io/badge/PHP-8.5%2B-blue)
![Laravel](https://img.shields.io/badge/Laravel-13-red)
![License](https://img.shields.io/badge/license-MIT-green)

A batteries-included starter kit featuring **dual authentication** (User + Admin), two-factor authentication, request logging, email-based error reporting, and a frontend notification system — all wired up and ready to extend.

---

## Features

### Authentication
- Dual guard system: independent `web` (User) and `admin` (Admin) guards
- Full auth flow per guard: login, register, forgot password, reset password, email verification
- Two-factor authentication (TOTP + recovery codes) for both guards
- Rate-limited login — 5 attempts per IP/email before throttle
- Separate password reset brokers per guard

### Frontend
- Inertia.js v3 SPA with Vue 3 + TypeScript (`<script setup lang="ts">`)
- Tailwind CSS v4 utility-first styling
- Reka UI component library
- Wayfinder — type-safe route/action imports auto-generated from controllers
- In-app notification system with cross-tab broadcast support

### Request Logging 
Every HTTP request is logged to a dedicated `request` log channel with:
- Unique request ID (`X-Request-Id` UUID header) for distributed tracing
- Method, path, route name, HTTP status code, and response duration
- Authenticated user ID, IP address, and user agent
- Sanitised request payload — passwords and tokens are automatically redacted
- Uploaded file metadata (name and count)

Sensitive field redaction is configured via `ERROR_REPORTING_SENSITIVE_FIELDS` in `.env`.

Learn more about it here: [Laravel Audit Trail: Building a System That Remembers](https://blog.shakiltech.com/laravel-audit-trail-building-a-system-that-remembers/)

### Error Reporting
Unhandled exceptions are captured and emailed to configured recipients with:
- Full stack trace, exception class, message, file, and line number
- Request context (method, URL, user, IP) correlated via request ID
- Throttled reporting — duplicates suppressed within a configurable window (default 300 s)
- Domain exceptions can carry business context via the `ContextualException` base class

Configure in `.env`:
```env
ERROR_REPORTING_ENABLED=true
ERROR_REPORTING_RECIPIENTS="you@example.com,team@example.com"
ERROR_REPORTING_THROTTLE_SECONDS=300
ERROR_REPORTING_SENSITIVE_FIELDS=password,password_confirmation,token
```

### Notification System
Backend flash messages are automatically surfaced as animated toast notifications via the `useNotifications` composable:
- Four types: `success`, `info`, `warning`, `danger`
- Auto-dismiss based on message length (3–8 seconds)
- Cross-tab sync via the `BroadcastChannel` API
- Spring-in / ease-out animations using the Web Animations API
- Integrated with Inertia page props — set a session flash in any controller and it appears automatically

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| PHP | 8.5 |
| Backend framework | Laravel 13 |
| Auth backend | Laravel Fortify |
| SPA bridge | Inertia.js v3 |
| Route typing | Laravel Wayfinder |
| Frontend | Vue 3 + TypeScript |
| Styling | Tailwind CSS v4 |
| UI components | Reka UI |
| Testing | Pest v4, PHPUnit 12 |
| Static analysis | Larastan v3, Rector v2 |
| Code style | Laravel Pint, ESLint 9, Prettier 3 |

---

## Requirements

- PHP ^8.5
- Composer ^2
- Node.js ^20
- SQLite (default), MySQL 8+, or PostgreSQL 15+

---

## Quick Start

```bash
git clone https://github.com/itxshakil/laravel-multi-auth-starter.git
cd laravel-multi-auth-starter
composer setup
composer run dev
```

`composer setup` installs PHP and npm dependencies, copies `.env.example` to `.env`, generates the app key, runs migrations, and builds frontend assets in one step.

## Manual Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

---

## Development

```bash
composer run dev   # server + queue listener + Pail log viewer + Vite (all in one)
npm run dev        # Vite only
```

---

## Default Credentials

Seeded automatically via `DatabaseSeeder`:

| Role | Email | Password |
|------|-------|----------|
| User | test@example.com | password |
| Admin | admin@example.com | password |

> Change these before deploying to any shared environment.

---

## Route Reference

### User Auth (via Fortify)

| Route | Purpose |
|-------|---------|
| `GET /login` | Login page |
| `GET /register` | Registration |
| `GET /forgot-password` | Request password reset |
| `GET /reset-password/{token}` | Set new password |
| `GET /verify-email` | Email verification notice |
| `GET /two-factor-challenge` | TOTP / recovery code entry |
| `GET /dashboard` | Protected user dashboard |

### Admin Auth (`/admin/`)

| Route | Purpose |
|-------|---------|
| `GET /admin/login` | Admin login |
| `GET /admin/register` | Admin registration |
| `GET /admin/forgot-password` | Admin password reset request |
| `GET /admin/reset-password/{token}` | Admin set new password |
| `GET /admin/verify-email` | Admin email verification |
| `GET /admin/two-factor-challenge` | Admin TOTP / recovery code entry |
| `GET /admin/dashboard` | Protected admin dashboard |

---

## Architecture

```
app/
├── Actions/                   # Single-purpose executors
│   ├── Admin/                 #   CreateNewAdmin, ResetAdminPassword
│   └── Fortify/               #   CreateNewUser, ResetUserPassword
├── Exceptions/
│   ├── ContextualException.php  # Base class for domain exceptions with context
│   └── ErrorReporter.php        # Throttled exception-to-email reporting
├── Http/
│   ├── Controllers/
│   │   └── Admin/Auth/        # LoginController, RegisterController,
│   │                          # TwoFactorController, EmailVerificationController,
│   │                          # PasswordResetLinkController, NewPasswordController
│   ├── Middleware/
│   │   ├── RequestLogger.php              # Per-request structured logging
│   │   └── RedirectIfAdminAuthenticated.php
│   └── Requests/Admin/Auth/   # LoginRequest, RegisterRequest
├── Models/
│   ├── User.php
│   └── Admin.php
└── Providers/
    ├── AppServiceProvider.php  # Model guard, Debugbar config
    └── FortifyServiceProvider.php

resources/js/
├── pages/
│   ├── auth/                  # User auth pages (Vue + Inertia)
│   └── admin/auth/            # Admin auth pages
├── components/
│   └── notifications/         # NotificationStack, NotificationItem
├── composables/
│   └── useNotifications.ts    # Toast notification composable
└── types/
    ├── auth.ts                # User, Admin, Auth types
    └── notifications.ts       # Notification, NotificationType types
```

---

## AI Guidelines (`.ai/`)

This project ships with a structured set of AI coding guidelines in `.ai/guidelines/` used by Claude Code and other Boost-compatible assistants:

| File | Coverage |
|------|---------|
| `00-forbidden.md` | Anti-patterns — fat controllers, raw SQL, `env()` outside config, etc. |
| `01-coding-standards.md` | PSR-12, naming conventions, FormRequests, API Resources |
| `02-architecture.md` | Layer responsibilities, folder mapping, constructor injection, DTOs |
| `03-database.md` | Migrations, indexing, Eloquent best practices, N+1 prevention |
| `04-testing.md` | Pest v4 syntax, AAA pattern, Fakes, 70% coverage target |
| `05-security.md` | Sanctum, CSRF, policy-first authorization |
| `06-performance.md` | Caching, deferred Inertia props, query optimisation |
| `07-frontend.md` | Wayfinder, `useForm`, TypeScript, Tailwind v4 conventions |

These rules are automatically loaded when working with Claude Code via `CLAUDE.md` / `AGENTS.md`.

---

## Testing

```bash
php artisan test --compact                # all tests
php artisan test --compact --filter=Auth  # filter by name
composer run ci:check                     # full CI: analyse + lint + format + types + tests
```

---

## Code Quality

```bash
vendor/bin/pint --dirty   # fix PHP style (Pint)
composer run analyse       # Larastan static analysis
npm run lint               # ESLint
npm run format             # Prettier
```

---

## License

MIT — free to use, modify, and distribute.
