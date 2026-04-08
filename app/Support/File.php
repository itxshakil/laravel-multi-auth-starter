<?php

declare(strict_types=1);

namespace App\Support;

use Exception;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class File
{
    public static function getMimeType(string $path): string
    {
        return new finfo(FILEINFO_MIME_TYPE)->file($path);
    }

    /**
     * @throws Exception
     */
    public static function getHash(string|UploadedFile $file, bool $raw = false): string
    {
        if ($file instanceof UploadedFile) {
            $file = $file->getRealPath();
        }

        try {
            $hash = hash_file('sha256', $file);

            return $raw ? $hash : base64_encode($hash);
        } catch (Exception $exception) {
            logger()->error(sprintf('Error while hashing file: %s File: %s', $exception->getMessage(), $file), $exception->getTrace());
            throw $exception;
        }
    }

    public static function uniqueName(string|UploadedFile $file): string
    {
        $originalName = $file instanceof UploadedFile
            ? $file->getClientOriginalName()
            : $file;

        $extension = self::getExtension($file);
        $baseName = Str::transliterate(self::cleanName($originalName));
        $random = Str::random(6);
        $suffix = sprintf('_%s.%s', $random, $extension);
        $maxBaseLength = 100 - strlen($suffix);

        return Str::limit($baseName, $maxBaseLength, '').$suffix;
    }

    public static function cleanName(string|UploadedFile $file): string
    {
        $name = $file instanceof UploadedFile
            ? $file->getClientOriginalName()
            : $file;

        $nameWithoutExtension = pathinfo($name, PATHINFO_FILENAME);
        $cleaned = preg_replace('/[^A-Za-z0-9_-]/', '_', $nameWithoutExtension);

        return empty($cleaned) ? 'file' : $cleaned;
    }

    public static function getExtension(string|UploadedFile $file): string
    {
        return $file instanceof UploadedFile
            ? $file->getClientOriginalExtension()
            : pathinfo($file, PATHINFO_EXTENSION);
    }
}
