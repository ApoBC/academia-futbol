<?php

namespace App\Http\Requests;

use App\Enums\RolUsuario;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        // Un admin (no super) solo puede crear profesores, sin importar
        // qué rol venga en el request: se ignora y se fuerza en el controller.
        $rolesPermitidos = $this->user()->hasRole('superadmin')
            ? array_column(RolUsuario::cases(), 'value')
            : [RolUsuario::Profesor->value];

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:usuarios,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol' => ['required', Rule::in($rolesPermitidos)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
