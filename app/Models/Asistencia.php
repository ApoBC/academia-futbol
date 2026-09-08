<?php

namespace App\Models;

use App\Enums\OrigenAsistencia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['alumno_id', 'profesor_id', 'fecha', 'hora', 'origen', 'sincronizado', 'dispositivo_sync'])]
class Asistencia extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'origen' => OrigenAsistencia::class,
            'sincronizado' => 'boolean',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }
}
