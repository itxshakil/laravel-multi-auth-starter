<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Middleware\RequestLogger;
use App\Mail\ExceptionOccurred;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use JsonException;
use Throwable;

final class ErrorReporter
{
    /**
     * @throws JsonException
     */
    public static function report(Throwable $throwable): void
    {
        if (! config('error-reporting.enabled', false)) {
            return;
        }

        $recipients = self::recipients();
        if ($recipients === []) {
            return;
        }

        $throttleKey = self::throttleKey($throwable, request());
        $throttleSeconds = max((int) config('error-reporting.throttle_seconds', 300), 1);

        if (! Cache::add($throttleKey, true, now()->addSeconds($throttleSeconds))) {
            return;
        }

        try {
            Mail::to($recipients)->send(new ExceptionOccurred(self::payload($throwable, request())));
        } catch (Throwable $mailException) {
            Log::error('error-reporting.delivery_failed', [
                'exception' => $mailException::class,
                'message' => $mailException->getMessage(),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private static function recipients(): array
    {
        /** @var array<int, string> $recipients */
        $recipients = config('error-reporting.recipients', []);

        return array_values(array_filter(
            $recipients,
            static fn (string $recipient): bool => filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false,
        ));
    }

    /**
     * @throws JsonException
     */
    private static function throttleKey(Throwable $throwable, ?Request $request): string
    {
        return 'error-reporting:'.hash('sha256', json_encode([
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'method' => $request?->method(),
            'url' => $request?->fullUrl(),
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<string, mixed>
     */
    private static function payload(Throwable $throwable, ?Request $request): array
    {
        return [
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
            'environment' => app()->environment(),
            'request_id' => self::requestId($request),
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
            'user_id' => $request?->user()?->getAuthIdentifier(),
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'query' => $request instanceof Request ? self::sanitizeData($request->query()) : [],
            'payload' => $request instanceof Request ? self::sanitizeData($request->request->all()) : [],
            'reported_at' => now()->toDateTimeString(),
        ];
    }

    private static function requestId(?Request $request): ?string
    {
        if (! $request instanceof Request) {
            return null;
        }

        $requestId = $request->attributes->get(RequestLogger::REQUEST_ID_ATTRIBUTE);

        if (is_string($requestId) && $requestId !== '') {
            return $requestId;
        }

        $headerValue = $request->header(RequestLogger::HEADER_NAME);

        return is_string($headerValue) && $headerValue !== '' ? $headerValue : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function sanitizeData(array $data): array
    {
        $sensitiveFields = array_map(Str::lower(...), config('error-reporting.sensitive_fields', []));
        $sanitized = [];

        foreach ($data as $key => $value) {
            $stringKey = (string) $key;

            if (in_array(Str::lower($stringKey), $sensitiveFields, true)) {
                $sanitized[$stringKey] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $sanitized[$stringKey] = self::sanitizeData($value);

                continue;
            }

            $sanitized[$stringKey] = $value;
        }

        return $sanitized;
    }
}
