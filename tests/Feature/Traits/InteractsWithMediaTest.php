<?php

use App\Models\Media;
use App\Models\User;
use App\Support\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// Named class so morphMany gets a consistent model_type
class InteractsWithMediaTestModel extends Model
{
    use InteractsWithMedia;

    protected $table = 'users';

    public $timestamps = false;
}

beforeEach(function (): void {
    Storage::fake('public');
    $user = User::factory()->create();
    $this->model = InteractsWithMediaTestModel::find($user->id);
});

it('can add a single media file', function (): void {
    $media = $this->model->addMedia(UploadedFile::fake()->image('photo.jpg'));

    expect($media)->toBeInstanceOf(Media::class)
        ->and($media->file_name)->toContain('.jpg')
        ->and($media->collection)->toBe('default');
});

it('can add multiple media files', function (): void {
    $result = $this->model->addMedias([
        UploadedFile::fake()->image('photo1.jpg'),
        UploadedFile::fake()->create('document.pdf'),
    ]);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2);
});

it('can add media to a named collection', function (): void {
    $media = $this->model->addMedia(UploadedFile::fake()->image('avatar.jpg'), 'gallery');

    expect($media->collection)->toBe('gallery');
});

it('can get all media in a collection', function (): void {
    $this->model->addMedia(UploadedFile::fake()->image('a.jpg'));
    $this->model->addMedia(UploadedFile::fake()->image('b.jpg'));
    $this->model->addMedia(UploadedFile::fake()->image('c.jpg'), 'gallery');

    expect($this->model->getMedia('default'))->toHaveCount(2)
        ->and($this->model->getMedia('gallery'))->toHaveCount(1);
});

it('can get the first media in a collection', function (): void {
    $this->model->addMedia(UploadedFile::fake()->image('first.jpg'));

    expect($this->model->getFirstMedia())->toBeInstanceOf(Media::class);
});

it('returns null for first media when collection is empty', function (): void {
    expect($this->model->getFirstMedia())->toBeNull();
});

it('can check whether a collection has media', function (): void {
    expect($this->model->hasMedia())->toBeFalse();

    $this->model->addMedia(UploadedFile::fake()->image('photo.jpg'));

    expect($this->model->hasMedia())->toBeTrue();
});

it('can soft delete a collection', function (): void {
    $this->model->addMedia(UploadedFile::fake()->image('photo.jpg'));

    expect($this->model->deleteMedia())->toBeTrue()
        ->and($this->model->hasMedia())->toBeFalse();
});

it('can soft delete all media across collections', function (): void {
    $this->model->addMedia(UploadedFile::fake()->image('a.jpg'));
    $this->model->addMedia(UploadedFile::fake()->image('b.jpg'), 'gallery');

    expect($this->model->deleteAllMedia())->toBeTrue()
        ->and($this->model->hasMedia('default'))->toBeFalse()
        ->and($this->model->hasMedia('gallery'))->toBeFalse();
});

it('stores the file on the configured disk', function (): void {
    $media = $this->model->addMedia(UploadedFile::fake()->image('photo.jpg'));

    Storage::disk('public')->assertExists($media->path);
});
