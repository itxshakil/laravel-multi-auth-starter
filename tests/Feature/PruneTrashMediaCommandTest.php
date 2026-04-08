<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
});

it('reports empty trash when there is nothing to prune', function (): void {
    $this->artisan('media:prune-trash', ['--force' => true])
        ->expectsOutput('Trash is already empty.')
        ->assertExitCode(0);
});

it('deletes all files in the trash folder', function (): void {
    Storage::disk('public')->put('trash/uploads/photo.jpg', 'data');
    Storage::disk('public')->put('trash/uploads/document.pdf', 'data');

    $this->artisan('media:prune-trash', ['--force' => true])
        ->expectsOutput('Pruned trash successfully.')
        ->assertExitCode(0);

    Storage::disk('public')->assertMissing('trash/uploads/photo.jpg');
    Storage::disk('public')->assertMissing('trash/uploads/document.pdf');
});

it('removes the trash directory after pruning', function (): void {
    Storage::disk('public')->put('trash/uploads/file.jpg', 'data');

    $this->artisan('media:prune-trash', ['--force' => true])->assertExitCode(0);

    expect(Storage::disk('public')->allFiles('trash'))->toBeEmpty();
});

it('accepts a custom disk option', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('trash/file.txt', 'data');

    $this->artisan('media:prune-trash', ['--disk' => 'local', '--force' => true])
        ->expectsOutput('Pruned trash successfully.')
        ->assertExitCode(0);

    Storage::disk('local')->assertMissing('trash/file.txt');
});

it('asks for confirmation when --force is not passed', function (): void {
    Storage::disk('public')->put('trash/file.jpg', 'data');

    $this->artisan('media:prune-trash')
        ->expectsConfirmation('Are you sure you want to permanently delete all trashed media?', 'no')
        ->expectsOutput('Prune operation cancelled.')
        ->assertExitCode(0);

    Storage::disk('public')->assertExists('trash/file.jpg');
});

it('proceeds when confirmation is accepted', function (): void {
    Storage::disk('public')->put('trash/file.jpg', 'data');

    $this->artisan('media:prune-trash')
        ->expectsConfirmation('Are you sure you want to permanently delete all trashed media?', 'yes')
        ->assertExitCode(0);

    Storage::disk('public')->assertMissing('trash/file.jpg');
});

it('writes output to the log', function (): void {
    Log::spy();

    $this->artisan('media:prune-trash', ['--force' => true]);

    Log::shouldHaveReceived('info')->with('Trash is already empty.', [])->once();
});

it('is scheduled to run weekly with --force', function (): void {
    $events = resolve(Schedule::class)->events();

    $command = collect($events)->first(
        fn ($e): bool => str_contains($e->command ?? '', 'media:prune-trash')
    );

    expect($command)->not->toBeNull()
        ->and($command->expression)->toBe('0 0 * * 0');
});
