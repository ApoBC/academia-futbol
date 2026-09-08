<?php

namespace App\Observers;

use App\Enums\AccionAuditoria;
use App\Enums\EstadoPago;
use App\Models\AuditoriaPago;
use App\Models\Pago;
use App\Services\CarneService;
use Illuminate\Support\Facades\Request;

class PagoObserver
{
    public function created(Pago $pago): void
    {
        $this->registrar($pago, AccionAuditoria::Creado, null, $pago->getAttributes());

        if ($pago->estado === EstadoPago::Confirmado) {
            $this->registrar($pago, AccionAuditoria::Confirmado, null, $pago->getAttributes());
            $this->generarCarneSiAplica($pago);
        }
    }

    /**
     * El carné se genera solo para planes con fecha de expiración (mensual/
     * trimestral/anual). Los packs se controlan por sesiones, no por vigencia,
     * así que no imprimen un carné con "vence hasta" (ver docs/Diseno_Base_Datos).
     */
    private function generarCarneSiAplica(Pago $pago): void
    {
        if ($pago->fecha_expiracion !== null) {
            app(CarneService::class)->generarParaPago($pago);
        }
    }

    public function updated(Pago $pago): void
    {
        $accion = $pago->wasChanged('estado') && $pago->estado === EstadoPago::Anulado
            ? AccionAuditoria::Anulado
            : AccionAuditoria::Editado;

        $this->registrar($pago, $accion, $pago->getOriginal(), $pago->getChanges());
    }

    private function registrar(Pago $pago, AccionAuditoria $accion, ?array $anteriores, ?array $nuevos): void
    {
        AuditoriaPago::create([
            'pago_id' => $pago->id,
            'admin_id' => $pago->admin_id,
            'accion' => $accion,
            'datos_anteriores' => $anteriores,
            'datos_nuevos' => $nuevos,
            'ip_origen' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}
