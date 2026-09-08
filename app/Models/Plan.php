<?php

namespace App\Models;

use App\Enums\TipoPlan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'tipo', 'duracion_dias', 'sesiones_max', 'precio', 'moneda', 'descripcion', 'activo'])]
class Plan extends Model
{
    protected $table = 'planes';

    protected function casts(): array
    {
        return [
            'tipo' => TipoPlan::class,
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function esPack(): bool
    {
        return $this->tipo === TipoPlan::Pack;
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }
}
