<?php

namespace App\Http\Resources;

use App\DataTransferObjects\EscaneoResultado;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property EscaneoResultado $resource
 */
class EscaneoAdminResource extends JsonResource
{
    /**
     * El admin ve todo lo del profesor, más la situación de pago/deuda.
     */
    public function toArray(Request $request): array
    {
        $alumno = $this->resource->alumno;

        return [
            'alumno' => [
                'id' => $alumno->id,
                'uuid' => $alumno->uuid,
                'nombre_completo' => $alumno->nombre_completo,
                'categoria' => $alumno->categoria?->label(),
                'alergias_enfermedades' => $alumno->alergias_enfermedades,
                'estado_salud_alerta' => $alumno->estado_salud_alerta,
                'estado' => $alumno->estado->value,
                'fecha_expiracion' => $alumno->fecha_expiracion?->format('Y-m-d'),
                'sesiones_restantes' => $alumno->sesiones_restantes,
            ],
            'semaforo' => $this->resource->semaforo->value,
            'ya_registrado_hoy' => $this->resource->yaRegistradoHoy,
        ];
    }
}
