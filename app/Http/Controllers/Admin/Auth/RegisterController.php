<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Actions\Admin\CreateNewAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('admin/auth/Register');
    }

    public function store(RegisterRequest $request, CreateNewAdmin $action): RedirectResponse
    {
        $admin = $action->create($request->validated());

        Auth::guard('admin')->login($admin);

        $request->session()->regenerate();

        return to_route('admin.dashboard');
    }
}
