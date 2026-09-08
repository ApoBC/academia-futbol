<?php

namespace App\Services;

use App\DataTransferObjects\EscaneoResultado;
use App\Enums\EstadoAlumno;
use App\Enums\MotivoFalloEscaneo;
use App\Enums\OrigenAsistencia;
use App\Enums\Semaforo;
use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\IntentoEscaneoFallido;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;

class EscaneoService
{
    /**
     * Procesa el escaneo de un QR: desencripta el UUID, valida al alumno,
     * calcula su semáforo y registra la asistencia del día (si no existía ya).
     *
     * Cualquier fallo (QR ilegible, alumno inexistente, alumno de baja) se
     * registra en intentos_escaneo_fallidos para detectar fraude o QRs viejos.
     */
    /**
     * @param  array{fecha?: string, hora?: string, dispositivo_sync?: string, sincronizado?: bool}  $datosAsistencia
     *                                                                                                                 Permite a la sincronización offline (Fase 5) reportar la fecha/hora real
     *                                                                                                                 del escaneo (capturado sin conexión) en vez de usar el momento actual.
     */
    public function escanear(string $contenidoQr, User $profesor, ?string $ipOrigen = null, array $datosAsistencia = []): EscaneoResultado
    {
        try {
            $uuid = Crypt::decryptString($contenidoQr);
        } catch (DecryptException) {
            return $this->registrarFallo($contenidoQr, $profesor, $ipOrigen, MotivoFalloEscaneo::DesencriptacionFallida);
        }

        $alumno = Alumno::where('uuid', $uuid)->first();

        if (! $alumno) {
            return $this->registrarFallo($contenidoQr, $profesor, $ipOrigen, MotivoFalloEscaneo::UuidNoEncontrado);
        }

        if ($alumno->estado === EstadoAlumno::Baja) {
            return $this->registrarFallo($contenidoQr, $profesor, $ipOrigen, MotivoFalloEscaneo::AlumnoBaja);
        }

        $yaRegistradoHoy = ! $this->registrarAsistencia($alumno, $profesor, $datosAsistencia);

        return EscaneoResultado::exito($alumno, $this->calcularSemaforo($alumno), $yaRegistradoHoy);
    }

    /**
     * Réplica de la vista v_escaneo_profesor (docs/Diseno_Base_Datos_Academia_Futbol.md).
     */
    public function calcularSemaforo(Alumno $alumno): Semaforo
    {
        if (in_array($alumno->estado, [EstadoAlumno::SinPago, EstadoAlumno::Baja], true)) {
            return Semaforo::Rojo;
        }

        if ($alumno->fecha_expiracion !== null) {
            if ($alumno->fecha_expiracion->greaterThanOrEqualTo(now()->startOfDay())) {
                return Semaforo::Verde;
            }

            if (now()->startOfDay()->diffInDays($alumno->fecha_expiracion, false) >= -7) {
                return Semaforo::Ambar;
            }

            return Semaforo::Rojo;
        }

        if ($alumno->sesiones_restantes !== null) {
            return $alumno->sesiones_restantes > 0 ? Semaforo::Verde : Semaforo::Rojo;
        }

        return Semaforo::Rojo;
    }

    /**
     * @param  array{fecha?: string, hora?: string, dispositivo_sync?: string, sincronizado?: bool}  $datosAsistencia
     * @return bool true si se creó una asistencia nueva, false si ya existía una para esa fecha.
     */
    private function registrarAsistencia(Alumno $alumno, User $profesor, array $datosAsistencia = []): bool
    {
        try {
            Asistencia::create([
                'alumno_id' => $alumno->id,
                'profesor_id' => $profesor->id,
                'fecha' => $datosAsistencia['fecha'] ?? now()->toDateString(),
                'hora' => $datosAsistencia['hora'] ?? now()->toTimeString(),
                'origen' => OrigenAsistencia::EscaneoQr,
                'sincronizado' => $datosAsistencia['sincronizado'] ?? true,
                'dispositivo_sync' => $datosAsistencia['dispositivo_sync'] ?? null,
            ]);

            return true;
        } catch (QueryException $e) {
            // Violación del UNIQUE(alumno_id, fecha): ya se había marcado hoy.
            if ($this->esErrorDeDuplicado($e)) {
                return false;
            }

            throw $e;
        }
    }

    private function esErrorDeDuplicado(QueryException $e): bool
    {
        return in_array((int) $e->errorInfo[1] ?? 0, [1062, 19, 2067], true);
    }

    private function registrarFallo(string $contenidoQr, User $profesor, ?string $ipOrigen, MotivoFalloEscaneo $motivo): EscaneoResultado
    {
        IntentoEscaneoFallido::create([
            'profesor_id' => $profesor->id,
            'contenido_qr' => $contenidoQr,
            'motivo_fallo' => $motivo,
            'ip_origen' => $ipOrigen,
        ]);

        return EscaneoResultado::fallo($motivo);
    }
}
