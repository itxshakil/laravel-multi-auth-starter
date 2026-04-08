<?php

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
});

it('returns 403 for unauthenticated requests', function (): void {
    $media = Media::factory()->create(['disk' => 'local']);

    $url = URL::temporarySignedRoute('media.temporary', now()->addMinutes(5), ['media' => $media->id]);

    $this->get($url)->assertForbidden();
});

it('returns 404 when the file does not exist on disk', function (): void {
    $user = User::factory()->create();
    $media = Media::factory()->create([
        'disk' => 'local',
        'path' => 'uploads/missing.jpg',
    ]);

    $url = URL::temporarySignedRoute('media.temporary', now()->addMinutes(5), ['media' => $media->id]);

    $this->actingAs($user)->get($url)->assertNotFound();
});

it('serves the file for authenticated users with a valid signed URL', function (): void {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('photo.jpg');
    $path = $file->storeAs('uploads', 'photo.jpg', 'local');

    $media = Media::factory()->create([
        'disk' => 'local',
        'path' => $path,
        'mime_type' => 'image/jpeg',
        'file_name' => 'photo.jpg',
        'name' => 'photo',
    ]);

    $url = URL::temporarySignedRoute('media.temporary', now()->addMinutes(5), ['media' => $media->id]);

    $this->actingAs($user)
        ->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg');
});

it('rejects expired signed URLs', function (): void {
    $user = User::factory()->create();
    $media = Media::factory()->create(['disk' => 'local']);

    $url = URL::temporarySignedRoute('media.temporary', now()->subMinute(), ['media' => $media->id]);

    $this->actingAs($user)->get($url)->assertForbidden();
});

it('rejects tampered signed URLs', function (): void {
    $user = User::factory()->create();
    $media = Media::factory()->create(['disk' => 'local']);
    $otherMedia = Media::factory()->create(['disk' => 'local']);

    $url = URL::temporarySignedRoute('media.temporary', now()->addMinutes(5), ['media' => $media->id]);
    // Swap media ID in the path — this invalidates the signature
    $tamperedUrl = str_replace(sprintf('/media/%s/', $media->id), sprintf('/media/%s/', $otherMedia->id), $url);

    $this->actingAs($user)->get($tamperedUrl)->assertForbidden();
});
