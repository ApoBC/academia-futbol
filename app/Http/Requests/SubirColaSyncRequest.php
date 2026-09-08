<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubirColaSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['profesor', 'admin']);
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.contenido_qr' => ['required', 'string'],
            'items.*.fecha' => ['required', 'date'],
            'items.*.hora' => ['required', 'date_format:H:i:s'],
            'dispositivo' => ['nullable', 'string', 'max:255'],
        ];
    }
}
