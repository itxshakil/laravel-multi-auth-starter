<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Traits\Console\LogsCommandMessages;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('media:prune-trash {--disk=public : The disk to prune trash from} {--force : Skip confirmation prompt}')]
#[Description('Permanently delete all files in the trash folder')]
class PruneTrashMediaCommand extends Command
{
    use LogsCommandMessages;

    public function handle(): int
    {
        $disk = (string) $this->option('disk');
        $force = (bool) $this->option('force');

        if (! $force && ! $this->confirm('Are you sure you want to permanently delete all trashed media?')) {
            $this->logInfo('Prune operation cancelled.');

            return self::SUCCESS;
        }

        $filesystem = Storage::disk($disk);

        if (! $filesystem->exists('trash')) {
            $this->logInfo('Trash is already empty.');

            return self::SUCCESS;
        }

        try {
            $filesystem->deleteDirectory('trash');
            $this->logInfo('Pruned trash successfully.');
        } catch (Exception $exception) {
            $this->logError('Error pruning trash: '.$exception->getMessage(), $exception->getTrace());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
