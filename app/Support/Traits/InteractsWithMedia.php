<?php

declare(strict_types=1);

namespace App\Support\Traits;

use App\Models\Media;
use App\Support\File;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

trait InteractsWithMedia
{
    /**
     * @return MorphMany<Media, $this>
     */
    public function medias(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    /**
     * @return MorphOne<Media, $this>
     */
    public function media(): MorphOne
    {
        return $this->morphOne(Media::class, 'model');
    }

    /**
     * @param  array<UploadedFile>  $files
     * @return Collection<int, Media>
     */
    public function addMedias(array $files, string $collection = 'default'): Collection
    {
        return collect($files)->map(fn (UploadedFile $file) => $this->addMedia($file, $collection));
    }

    public function addMedia(UploadedFile $file, string $collection = 'default'): Media
    {
        return $this->addMediaAs($file, $collection);
    }

    public function addMediaAs(UploadedFile $file, string $collection = 'default', ?string $name = null): Media
    {
        $uniqueName = $name ?? File::uniqueName($file);

        return $this->medias()->create([
            'name' => File::cleanName($file->getClientOriginalName()),
            'file_name' => $uniqueName,
            'mime_type' => $file->getMimeType(),
            'path' => $file->storeAs($this->getMediaDirectory($collection), $uniqueName, $this->getMediaDisk($collection)),
            'disk' => $this->getMediaDisk($collection),
            'file_hash' => File::getHash($file),
            'collection' => $collection,
            'size' => $file->getSize(),
        ]);
    }

    public function copyMedia(Media $media, string $collection = 'default'): Media
    {
        $newFileName = File::uniqueName($media->file_name);
        $newPath = $this->getMediaDirectory($collection).'/'.$newFileName;

        Storage::disk($media->disk)->copy($media->path, $newPath);

        return $this->medias()->create([
            'name' => $media->name,
            'file_name' => $newFileName,
            'mime_type' => $media->mime_type,
            'path' => $newPath,
            'disk' => $this->getMediaDisk($collection),
            'file_hash' => $media->file_hash,
            'collection' => $collection,
            'size' => $media->size,
        ]);
    }

    /**
     * @return EloquentCollection<int, Media>
     */
    public function getMedia(string $collection = 'default'): EloquentCollection
    {
        return $this->medias()->where('collection', $collection)->get();
    }

    public function getFirstMedia(string $collection = 'default'): ?Media
    {
        return $this->medias()->where('collection', $collection)->first();
    }

    public function getFirstMediaUrl(string $collection = 'default'): ?string
    {
        return $this->getFirstMedia($collection)?->full_url;
    }

    public function hasMedia(string $collection = 'default'): bool
    {
        return $this->medias()->where('collection', $collection)->exists();
    }

    public function deleteMedia(string $collection = 'default'): bool
    {
        $this->medias()->where('collection', $collection)->get()->each->delete();

        return true;
    }

    public function deleteAllMedia(): bool
    {
        $this->medias()->get()->each->delete();

        return true;
    }

    public function clearMediaCollection(string $collection = 'default'): void
    {
        $this->medias()->where('collection', $collection)->get()->each->delete();
    }

    public function getMediaDirectory(string $collection): string
    {
        return 'uploads';
    }

    public function getMediaDisk(string $collection): string
    {
        return 'public';
    }

    protected static function bootInteractsWithMedia(): void
    {
        static::deleted(static function (self $model): void {
            if (! $model->forceDeleting && in_array(SoftDeletes::class, class_uses($model), true)) {
                return;
            }

            $model->medias()->get()->each->delete();
        });
    }
}
