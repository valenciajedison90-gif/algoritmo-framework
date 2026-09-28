<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('empresa') ?? $this->input('id');

        return [
            'nit' => 'required|string|max:30',
            'razon_social' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nit.required' => 'El NIT o documento de identificación de la empresa es obligatorio.',
            'nit.max' => 'El NIT no debe superar los 30 caracteres.',
            'razon_social.required' => 'La razón social o nombre de la empresa es obligatorio.',
            'razon_social.max' => 'La razón social no debe superar los 255 caracteres.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
        ];
    }
}
