<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Alumno::class);
    }

    public function rules(): array
    {
        return [
            'padre_id' => ['required', 'exists:usuarios,id'],
            'nombre_completo' => ['required', 'string', 'max:150'],
            'dni' => ['nullable', 'string', 'max:20', Rule::unique('alumnos', 'dni')],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'sexo' => ['nullable', Rule::in(['M', 'F'])],
            'alergias_enfermedades' => ['nullable', 'string'],
            'estado_salud_alerta' => ['boolean'],
        ];
    }
}
