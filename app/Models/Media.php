<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Override;

/**
 * @property-read string $full_url
 */
#[Fillable([
    'model_type',
    'model_id',
    'name',
    'file_name',
    'mime_type',
    'path',
    'disk',
    'file_hash',
    'collection',
    'order',
    'size',
])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $appends = ['full_url'];

    #[Override]
    protected static function booted(): void
    {
        static::deleted(static function (self $media): void {
            $disk = Storage::disk($media->disk);

            if ($disk->exists($media->path)) {
                $disk->move(
                    $media->path,
                    'trash/'.dirname($media->path).'/'.basename($media->path)
                );
            } else {
                logger()->warning('Media file not found on disk: '.$media->path);
            }
        });

        static::updated(static function (self $media): void {
            if ($media->wasChanged('path', 'disk')) {
                $disk = Storage::disk($media->getOriginal('disk'));
                $originalPath = $media->getOriginal('path');

                if ($disk->exists($originalPath)) {
                    $disk->move($originalPath, 'trash/'.$originalPath);
                }
            }
        });
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    protected function fullUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->disk === 'local') {
                if (! auth()->check()) {
                    return '#';
                }

                return URL::temporarySignedRoute(
                    'media.temporary',
                    now()->addMinutes(5),
                    ['media' => $this->id]
                );
            }

            return Storage::disk($this->disk)->url($this->path);
        });
    }

    protected function extension(): Attribute
    {
        return Attribute::get(fn (): string => pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    protected function humanReadableSize(): Attribute
    {
        return Attribute::get(fn (): string => Number::fileSize((int) $this->size));
    }
}
