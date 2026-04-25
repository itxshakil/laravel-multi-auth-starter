<?php

declare(strict_types=1);

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin registration screen can be rendered', function (): void {
    $this->get(route('admin.register'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/auth/Register'));
});

test('new admins can register', function (): void {
    $this->post('/admin/register', [
        'name' => 'Test Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticatedAs(Admin::first(), 'admin');
});

test('admin registration requires a unique email', function (): void {
    Admin::factory()->create(['email' => 'existing@example.com']);

    $this->post('/admin/register', [
        'name' => 'Another Admin',
        'email' => 'existing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('admin');
});

test('admin registration requires password confirmation', function (): void {
    $this->post('/admin/register', [
        'name' => 'Test Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    $this->assertGuest('admin');
});

test('authenticated admins are redirected away from register screen', function (): void {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.register'))
        ->assertRedirect(route('admin.dashboard'));
});
