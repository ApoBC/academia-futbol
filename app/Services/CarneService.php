<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\CarneQr;
use App\Models\Pago;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CarneService
{
    /**
     * Genera (o regenera) el carné QR de un alumno a partir de un pago confirmado.
     *
     * El QR nunca contiene datos legibles del alumno: solo su UUID encriptado
     * con la clave de la app (Crypt::encryptString). Al escanear, se desencripta
     * y se busca al alumno por UUID (ver Fase 4).
     *
     * Solo puede existir un carné activo por alumno: al generar uno nuevo,
     * el anterior se desactiva pero no se borra (versionado para auditoría).
     */
    public function generarParaPago(Pago $pago): CarneQr
    {
        return DB::transaction(function () use ($pago) {
            /** @var Alumno $alumno */
            $alumno = $pago->alumno;

            $anterior = $alumno->carneActivo();
            $anterior?->update(['activo' => false]);

            return CarneQr::create([
                'alumno_id' => $alumno->id,
                'pago_id' => $pago->id,
                'uuid_encriptado' => Crypt::encryptString($alumno->uuid),
                'version' => ($anterior?->version ?? 0) + 1,
                'fecha_expiracion' => $pago->fecha_expiracion ?? $alumno->fecha_expiracion,
                'activo' => true,
            ]);
        });
    }
}
