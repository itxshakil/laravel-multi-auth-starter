<?php

declare(strict_types=1);

namespace App\Support\Traits\Console;

trait LogsCommandMessages
{
    public function logInfo(string $message, array $context = []): void
    {
        $this->info($message);
        logger()->info($message, $context);
    }

    public function logError(string $message, array $context = []): void
    {
        $this->error($message);
        logger()->error($message, $context);
    }
}
