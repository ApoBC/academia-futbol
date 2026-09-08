<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'profesor_id',
    'fecha_ultima_sync',
    'cantidad_registros_cache',
    'hash_datos',
    'version_app',
    'asistencias_pendientes_sync',
])]
class ConfiguracionOffline extends Model
{
    protected $table = 'configuracion_offline';

    protected function casts(): array
    {
        return [
            'fecha_ultima_sync' => 'datetime',
        ];
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }
}
