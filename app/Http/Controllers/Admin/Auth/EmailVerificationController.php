<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): Response|RedirectResponse
    {
        /** @var Admin $admin */
        $admin = $request->user('admin');

        if ($admin->hasVerifiedEmail()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return Inertia::render('admin/auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = $request->user('admin');

        if (! $admin->hasVerifiedEmail()) {
            $admin->markEmailAsVerified();
            event(new Verified($admin));
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function send(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = $request->user('admin');

        if ($admin->hasVerifiedEmail()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        $admin->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
