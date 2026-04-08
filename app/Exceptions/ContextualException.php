<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Middleware\RequestLogger;
use RuntimeException;
use Throwable;

abstract class ContextualException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        protected readonly array $context = [],
        protected readonly ?string $publicMessage = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function publicMessage(): string
    {
        return $this->publicMessage ?? $this->getMessage();
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        $context = $this->context;
        $requestId = request()?->attributes->get(RequestLogger::REQUEST_ID_ATTRIBUTE)
            ?? request()?->header(RequestLogger::HEADER_NAME);

        if (is_string($requestId) && $requestId !== '') {
            $context['request_id'] = $requestId;
        }

        return $context;
    }
}
