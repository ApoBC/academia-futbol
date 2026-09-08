<?php

namespace App\Models;

use App\Enums\CategoriaAlumno;
use App\Enums\EstadoAlumno;
use App\Services\CategoriaService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'padre_id',
    'nombre_completo',
    'dni',
    'fecha_nacimiento',
    'sexo',
    'alergias_enfermedades',
    'estado_salud_alerta',
])]
class Alumno extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Alumno $alumno): void {
            $alumno->uuid ??= (string) Str::uuid();
            $alumno->categoria ??= app(CategoriaService::class)
                ->calcular($alumno->fecha_nacimiento)
                ->value;
            $alumno->estado ??= EstadoAlumno::SinPago->value;
        });

        static::updating(function (Alumno $alumno): void {
            if ($alumno->isDirty('fecha_nacimiento')) {
                $alumno->categoria = app(CategoriaService::class)
                    ->calcular($alumno->fecha_nacimiento)
                    ->value;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_expiracion' => 'date',
            'categoria' => CategoriaAlumno::class,
            'estado' => EstadoAlumno::class,
            'estado_salud_alerta' => 'boolean',
        ];
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'padre_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function carnes(): HasMany
    {
        return $this->hasMany(CarneQr::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    public function carneActivo(): ?CarneQr
    {
        return $this->carnes()->where('activo', true)->latest('version')->first();
    }
}
