<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambioPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_actual' => 'nullable|string',
            'nueva_password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'nueva_password.required' => 'La nueva contraseña es obligatoria.',
            'nueva_password.min' => 'La nueva contraseña debe tener mínimo 8 caracteres.',
            'nueva_password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
