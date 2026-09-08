<?php

namespace App\Services;

use App\Enums\EstadoAlumno;
use App\Enums\EstadoPago;
use App\Enums\TipoPlan;
use App\Models\Alumno;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PagoService
{
    /**
     * Registra un pago y actualiza la vigencia del alumno.
     *
     * Regla de negocio (docs/Diseno_Base_Datos_Academia_Futbol.md):
     *  - Si el alumno está al día (fecha_expiracion >= hoy), la nueva vigencia
     *    se suma DESDE la expiración anterior (no se pierden días pagados).
     *  - Si está moroso o nunca pagó, la nueva vigencia se cuenta DESDE hoy
     *    (no se regalan los días vencidos).
     *  - Un plan tipo "pack" no maneja fechas de expiración, sino sesiones.
     */
    public function registrarPago(Alumno $alumno, Plan $plan, User $admin, array $datos): Pago
    {
        return DB::transaction(function () use ($alumno, $plan, $admin, $datos) {
            $fechaPago = isset($datos['fecha_pago']) ? Carbon::parse($datos['fecha_pago']) : now();
            $estado = EstadoPago::from($datos['estado'] ?? EstadoPago::Confirmado->value);

            $esPack = $plan->tipo === TipoPlan::Pack;

            $fechaExpiracion = null;
            $sesionesOtorgadas = null;

            if ($esPack) {
                $sesionesOtorgadas = $plan->sesiones_max;
            } else {
                $fechaExpiracion = $this->calcularNuevaExpiracion($alumno->fecha_expiracion, $plan->duracion_dias);
            }

            $pago = Pago::create([
                'alumno_id' => $alumno->id,
                'plan_id' => $plan->id,
                'admin_id' => $admin->id,
                'monto' => $datos['monto'] ?? $plan->precio,
                'moneda' => $datos['moneda'] ?? $plan->moneda,
                'metodo_pago' => $datos['metodo_pago'],
                'numero_operacion' => $datos['numero_operacion'] ?? null,
                'fecha_pago' => $fechaPago,
                'fecha_inicio_vigencia' => $fechaPago,
                'fecha_expiracion' => $fechaExpiracion,
                'sesiones_otorgadas' => $sesionesOtorgadas,
                'estado' => $estado,
                'notas' => $datos['notas'] ?? null,
            ]);

            if ($estado === EstadoPago::Confirmado) {
                $this->aplicarPagoAlAlumno($alumno, $esPack, $fechaExpiracion, $sesionesOtorgadas);
            }

            return $pago;
        });
    }

    /**
     * Calcula la nueva fecha de expiración según la regla "al día vs. moroso".
     */
    public function calcularNuevaExpiracion(?Carbon $fechaExpiracionActual, int $duracionDias): Carbon
    {
        $hoy = Carbon::today();

        if ($fechaExpiracionActual && $fechaExpiracionActual->greaterThanOrEqualTo($hoy)) {
            // Al día: se suma desde la expiración anterior.
            return $fechaExpiracionActual->copy()->addDays($duracionDias);
        }

        // Moroso o primera vez: se suma desde hoy.
        return $hoy->copy()->addDays($duracionDias);
    }

    private function aplicarPagoAlAlumno(Alumno $alumno, bool $esPack, ?Carbon $fechaExpiracion, ?int $sesionesOtorgadas): void
    {
        if ($esPack) {
            $alumno->sesiones_restantes = ($alumno->sesiones_restantes ?? 0) + $sesionesOtorgadas;
        } else {
            $alumno->fecha_expiracion = $fechaExpiracion;
        }

        $alumno->estado = EstadoAlumno::Activo;
        $alumno->save();
    }
}
