<?php

namespace App\Models;

use App\Enums\MotivoFalloEscaneo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['profesor_id', 'contenido_qr', 'motivo_fallo', 'ip_origen'])]
class IntentoEscaneoFallido extends Model
{
    protected $table = 'intentos_escaneo_fallidos';

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'motivo_fallo' => MotivoFalloEscaneo::class,
        ];
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }
}
