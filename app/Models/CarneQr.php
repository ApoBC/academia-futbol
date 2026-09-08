<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['alumno_id', 'pago_id', 'uuid_encriptado', 'version', 'fecha_expiracion', 'activo'])]
class CarneQr extends Model
{
    protected $table = 'carnes_qr';

    protected function casts(): array
    {
        return [
            'fecha_generacion' => 'datetime',
            'fecha_expiracion' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }
}
