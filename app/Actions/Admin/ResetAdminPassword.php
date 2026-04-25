<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ResetAdminPassword
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function reset(Admin $admin, array $input): void
    {
        Validator::make($input, [
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ])->validate();

        $admin->forceFill([
            'password' => $input['password'],
        ])->save();
    }
}
