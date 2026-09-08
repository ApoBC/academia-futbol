<?php

namespace App\Http\Resources;

use App\DataTransferObjects\EscaneoResultado;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property EscaneoResultado $resource
 */
class EscaneoProfesorResource extends JsonResource
{
    /**
     * El profesor nunca ve montos de deuda ni datos de pago, solo lo
     * necesario para decidir si el alumno puede entrenar hoy.
     */
    public function toArray(Request $request): array
    {
        $alumno = $this->resource->alumno;

        return [
            'alumno' => [
                'nombre_completo' => $alumno->nombre_completo,
                'categoria' => $alumno->categoria?->label(),
                'alergias_enfermedades' => $alumno->alergias_enfermedades,
                'estado_salud_alerta' => $alumno->estado_salud_alerta,
            ],
            'semaforo' => $this->resource->semaforo->value,
            'ya_registrado_hoy' => $this->resource->yaRegistradoHoy,
        ];
    }
}
