<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('admin/auth/Login', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->incrementAttempts();

            return back()->withErrors([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->clearAttempts();

        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        if ($admin->hasEnabledTwoFactorAuthentication()) {
            Auth::guard('admin')->logout();

            $request->session()->put('admin_login.id', $admin->id);
            $request->session()->put('admin_login.remember', $remember);

            return to_route('admin.two-factor.login');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('admin.login');
    }
}
