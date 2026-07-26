<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

use Override;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'current_password.required'         => 'The current password field is required.',
            'current_password.current_password' => 'The provided password does not match your current password.',

            'password.required'  => 'The new password field is required.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
