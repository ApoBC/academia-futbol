<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\ConfiguracionOffline;
use App\Models\User;

class SyncService
{
    public function __construct(private readonly EscaneoService $escaneoService) {}

    /**
     * Arma el paquete que el profesor descarga antes de entrenar: un alumno
     * por cada carné QR activo, con el contenido exacto que se leerá en el
     * escaneo (uuid_encriptado) y su semáforo ya calculado, para poder
     * validar sin conexión. Actualiza también su configuracion_offline.
     *
     * @return array<int, array<string, mixed>>
     */
    public function paqueteDescarga(User $profesor): array
    {
        $alumnos = Alumno::whereHas('carnes', fn ($q) => $q->where('activo', true))
            ->with(['carnes' => fn ($q) => $q->where('activo', true)])
            ->get();

        $paquete = $alumnos->map(function (Alumno $alumno) {
            $carne = $alumno->carnes->first();

            return [
                'contenido_qr' => $carne->uuid_encriptado,
                'nombre_completo' => $alumno->nombre_completo,
                'categoria' => $alumno->categoria?->label(),
                'alergias_enfermedades' => $alumno->alergias_enfermedades,
                'estado_salud_alerta' => $alumno->estado_salud_alerta,
                'semaforo' => $this->escaneoService->calcularSemaforo($alumno)->value,
            ];
        })->values()->all();

        $this->actualizarConfiguracion($profesor, $paquete);

        return $paquete;
    }

    /**
     * Procesa la cola de escaneos capturados sin conexión. Reutiliza
     * EscaneoService (misma validación, mismo anti-duplicado) pero respetando
     * la fecha/hora real en la que se hizo el escaneo offline.
     *
     * @param  array<int, array{contenido_qr: string, fecha: string, hora: string}>  $items
     * @return array<int, array<string, mixed>>
     */
    public function procesarCola(array $items, User $profesor, ?string $ipOrigen, ?string $dispositivo): array
    {
        $resultados = [];

        foreach ($items as $item) {
            $resultado = $this->escaneoService->escanear(
                $item['contenido_qr'],
                $profesor,
                $ipOrigen,
                [
                    'fecha' => $item['fecha'],
                    'hora' => $item['hora'],
                    'sincronizado' => true,
                    'dispositivo_sync' => $dispositivo,
                ],
            );

            $resultados[] = [
                'contenido_qr' => $item['contenido_qr'],
                'ok' => $resultado->exitoso,
                'ya_registrado' => $resultado->yaRegistradoHoy,
                'motivo' => $resultado->motivoFallo?->value,
            ];
        }

        ConfiguracionOffline::updateOrCreate(
            ['profesor_id' => $profesor->id],
            ['fecha_ultima_sync' => now(), 'asistencias_pendientes_sync' => 0],
        );

        return $resultados;
    }

    /**
     * @param  array<int, array<string, mixed>>  $paquete
     */
    private function actualizarConfiguracion(User $profesor, array $paquete): void
    {
        ConfiguracionOffline::updateOrCreate(
            ['profesor_id' => $profesor->id],
            [
                'fecha_ultima_sync' => now(),
                'cantidad_registros_cache' => count($paquete),
                // Permite al cliente comparar y saltarse la recarga si nada cambió.
                'hash_datos' => hash('sha256', json_encode($paquete)),
            ],
        );
    }
}
