<?php

declare(strict_types=1);

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
use Illuminate\Support\Facades\RateLimiter;

test('admin login screen can be rendered', function (): void {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/auth/Login'));
});

test('admins can authenticate using the login screen', function (): void {
    $admin = Admin::factory()->create();

    $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticatedAs($admin, 'admin');
});

test('admins cannot authenticate with invalid password', function (): void {
    $admin = Admin::factory()->create();

    $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('admin');
});

test('admins with two factor enabled are redirected to two factor challenge', function (): void {
    $admin = Admin::factory()->create();

    $admin->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.two-factor.login'));

    $this->assertGuest('admin');
});

test('admins can logout', function (): void {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest('admin');
});

test('admins are rate limited on login', function (): void {
    $admin = Admin::factory()->create();

    RateLimiter::increment('admin-login|'.strtolower((string) $admin->email).'|127.0.0.1', amount: 5);

    $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');
});

test('authenticated admins are redirected away from login screen', function (): void {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.login'))
        ->assertRedirect(route('admin.dashboard'));
});
