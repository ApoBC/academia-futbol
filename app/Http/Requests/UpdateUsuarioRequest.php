<?php

namespace App\Http\Requests;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('usuario'));
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');

        $rolesPermitidos = $this->user()->hasRole('superadmin')
            ? array_column(RolUsuario::cases(), 'value')
            : [RolUsuario::Profesor->value];

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol' => ['required', Rule::in($rolesPermitidos)],
            'activo' => ['boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
