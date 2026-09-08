<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EscanearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['profesor', 'admin']);
    }

    public function rules(): array
    {
        return [
            'contenido_qr' => ['required', 'string'],
        ];
    }
}
