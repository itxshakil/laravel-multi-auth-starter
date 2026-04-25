<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Actions\Admin\ResetAdminPassword;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('admin/auth/ResetPassword', [
            'email' => $request->string('email'),
            'token' => $request->route('token'),
        ]);
    }

    public function store(Request $request, ResetAdminPassword $action): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Admin $admin) use ($request, $action): void {
                $action->reset($admin, $request->only('password', 'password_confirmation'));
                event(new PasswordReset($admin));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return to_route('admin.login')->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
