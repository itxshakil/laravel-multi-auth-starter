<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shares flash props with null values when no flash is set', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(
            fn ($page) => $page
                ->where('success', null)
                ->where('warning', null)
                ->where('info', null)
                ->where('error', null),
        );
});

it('shares success flash from session', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['success' => 'Profile updated.'])
        ->get('/dashboard')
        ->assertInertia(
            fn ($page) => $page->where('success', 'Profile updated.'),
        );
});

it('shares error flash from session', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['error' => 'Something went wrong.'])
        ->get('/dashboard')
        ->assertInertia(
            fn ($page) => $page->where('error', 'Something went wrong.'),
        );
});
