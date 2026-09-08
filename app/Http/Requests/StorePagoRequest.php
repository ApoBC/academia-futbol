<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Pago::class);
    }

    public function rules(): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'plan_id' => ['required', 'exists:planes,id'],
            'monto' => ['nullable', 'numeric', 'min:0'],
            'metodo_pago' => ['required', Rule::in(['efectivo', 'transferencia', 'tarjeta', 'yape', 'plin', 'otro'])],
            'numero_operacion' => ['nullable', 'string', 'max:100'],
            'fecha_pago' => ['nullable', 'date'],
            'estado' => ['nullable', Rule::in(['confirmado', 'pendiente', 'anulado'])],
            'notas' => ['nullable', 'string'],
        ];
    }
}
