<?php

namespace App\Models;

use App\Enums\AccionAuditoria;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pago_id', 'admin_id', 'accion', 'datos_anteriores', 'datos_nuevos', 'ip_origen', 'user_agent'])]
class AuditoriaPago extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'accion' => AccionAuditoria::class,
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
