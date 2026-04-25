<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('admin_login.id')) {
            return to_route('admin.login');
        }

        return Inertia::render('admin/auth/TwoFactorChallenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('admin_login.id');

        if (! $adminId) {
            return to_route('admin.login');
        }

        $admin = Admin::findOrFail($adminId);

        if ($request->filled('code')) {
            $valid = resolve(TwoFactorAuthenticationProvider::class)
                ->verify(decrypt($admin->two_factor_secret), $request->string('code')->toString());
        } elseif ($request->filled('recovery_code')) {
            $codes = json_decode((string) decrypt($admin->two_factor_recovery_codes), true);
            $recoveryCode = $request->string('recovery_code')->toString();
            $valid = in_array($recoveryCode, $codes, true);

            if ($valid) {
                $admin->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(
                        array_values(array_filter($codes, fn (string $c): bool => $c !== $recoveryCode))
                    )),
                ])->save();
            }
        } else {
            $valid = false;
        }

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        Auth::guard('admin')->login($admin, $request->session()->get('admin_login.remember', false));
        $request->session()->forget(['admin_login.id', 'admin_login.remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
