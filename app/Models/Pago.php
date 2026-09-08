<?php

namespace App\Models;

use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Observers\PagoObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'alumno_id',
    'plan_id',
    'admin_id',
    'monto',
    'moneda',
    'metodo_pago',
    'numero_operacion',
    'fecha_pago',
    'fecha_inicio_vigencia',
    'fecha_expiracion',
    'sesiones_otorgadas',
    'estado',
    'notas',
])]
#[ObservedBy(PagoObserver::class)]
class Pago extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Pago $pago): void {
            $pago->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'metodo_pago' => MetodoPago::class,
            'fecha_pago' => 'date',
            'fecha_inicio_vigencia' => 'date',
            'fecha_expiracion' => 'date',
            'estado' => EstadoPago::class,
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(AuditoriaPago::class);
    }
}
