<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateNewAdmin
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): Admin
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(Admin::class)],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ])->validate();

        return Admin::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }
}
