---
name: php-code-style
description: Apply PHP and Laravel coding style conventions when creating, editing, reviewing, or refactoring .php or .blade.php files.
---

# PHP Code Style

## Validation Rules

Use array notation for all validation rules; never use pipe-delimited strings.

```php
// Good
$request->validate([
    'email' => ['required', 'email', 'max:255'],
]);

// Avoid
$request->validate([
    'email' => 'required|email|max:255',
]);
```

## Routes

Use tuple array syntax for route definitions:

```php
// Good
Route::get('/users', [UsersController::class, 'index']);

// Avoid
Route::get('/users', 'UsersController@index');
```

- Use kebab-case for URL segments: `/my-resource/{myParam}`
- Use camelCase for route names: `Route::get(...)->name('myResource.index')`
- Use camelCase for route parameters: `{userId}`, `{postSlug}`
- Name resource controllers in the plural form: `UsersController`, `PostsController`

## Service Configuration

Add all third-party service credentials and endpoints to `config/services.php`; do not create new top-level config files for individual services.

```php
// config/services.php
'stripe' => [
    'key' => config('services.stripe.key'),
    'secret' => env('STRIPE_SECRET'),
],
```

## Docblocks

Do not add docblocks to methods or properties that already have complete PHP type hints. Only add a docblock when types alone cannot express the full contract (e.g., array shapes, union type nuance).

```php
// Good — types are self-documenting
public function findByEmail(string $email): ?User
{
    // ...
}

// Good — array shape warrants a docblock
/** @param array{name: string, email: string} $data */
public function create(array $data): User
{
    // ...
}

// Avoid — docblock adds nothing
/**
 * Find a user by email.
 *
 * @param string $email
 * @return User|null
 */
public function findByEmail(string $email): ?User
{
    // ...
}
```

## `final` and `readonly`

Do not apply `final` or `readonly` to classes by default. Use them only when the design explicitly requires it:
- `final` — when the class must not be extended
- `readonly` on properties — when the value must be truly immutable after construction

## Translations

Use `__()` for all translation strings. Do not use the `@lang` Blade directive.

```php
// Good
__('auth.failed')

// Avoid in Blade
@lang('auth.failed')
```

## String Interpolation

Prefer string interpolation over concatenation when embedding variables in strings.

```php
// Good
$message = "Hello, {$user->name}!";

// Avoid
$message = 'Hello, ' . $user->name . '!';
```
