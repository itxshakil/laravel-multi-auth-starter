<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    public const string HEADER_NAME = 'X-Request-Id';

    public const string REQUEST_ID_ATTRIBUTE = 'request_id';

    /**
     * @var array<int, string>
     */
    private array $excludedPaths = [
        'up',
    ];

    /**
     * @var array<int, string>
     */
    private readonly array $sensitiveFields;

    public function __construct()
    {
        /** @var array<int, string> $fields */
        $fields = config('error-reporting.sensitive_fields', [
            'password',
            'password_confirmation',
            'current_password',
            'token',
        ]);

        $this->sensitiveFields = $fields;
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();

        $request->attributes->set(self::REQUEST_ID_ATTRIBUTE, $requestId);
        $request->headers->set(self::HEADER_NAME, $requestId);

        Log::shareContext([
            'request_id' => $requestId,
        ]);

        $startedAt = hrtime(true);

        $response = $next($request);
        $response->headers->set(self::HEADER_NAME, $requestId);

        if ($this->shouldLogRequest($request)) {
            $this->logRequest($request, $response, $requestId, $startedAt);
        }

        return $response;
    }

    private function shouldLogRequest(Request $request): bool
    {
        return array_all($this->excludedPaths, fn ($path): bool => ! $request->is(trim((string) $path, '/')));
    }

    private function logRequest(Request $request, Response $response, string $requestId, int $startedAt): void
    {
        $context = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->getPathInfo(),
            'route_name' => $request->route()?->getName(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            'user_id' => $request->user()?->getAuthIdentifier(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        $query = $this->sanitizeData($request->query());
        if ($query !== []) {
            $context['query'] = $query;
        }

        $payload = $this->sanitizeData($request->request->all());
        if ($payload !== []) {
            $context['payload'] = $payload;
        }

        $uploadedFiles = $this->uploadedFilesSummary($request->allFiles());
        if ($uploadedFiles !== null) {
            $context['uploaded_files'] = $uploadedFiles;
        }

        Log::channel('request')->info('request.completed', $context);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $stringKey = (string) $key;

            if ($this->isSensitiveField($stringKey)) {
                $sanitized[$stringKey] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $sanitized[$stringKey] = $this->sanitizeData($value);

                continue;
            }

            $sanitized[$stringKey] = $value;
        }

        return $sanitized;
    }

    private function isSensitiveField(string $key): bool
    {
        return in_array(Str::lower($key), array_map(Str::lower(...), $this->sensitiveFields), true);
    }

    /**
     * @param  array<string, UploadedFile|array<int|string, UploadedFile|array>>  $files
     * @return array{count: int, names: array<int, string>}|null
     */
    private function uploadedFilesSummary(array $files): ?array
    {
        $names = $this->flattenUploadedFileNames($files);

        if ($names === []) {
            return null;
        }

        return [
            'count' => count($names),
            'names' => $names,
        ];
    }

    /**
     * @param  array<int|string, UploadedFile|array<int|string, UploadedFile|array>>  $files
     * @return array<int, string>
     */
    private function flattenUploadedFileNames(array $files): array
    {
        $names = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $names[] = $file->getClientOriginalName();

                continue;
            }

            if (is_array($file)) {
                array_push($names, ...$this->flattenUploadedFileNames($file));
            }
        }

        return $names;
    }
}
