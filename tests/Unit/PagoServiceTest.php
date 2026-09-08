<?php

namespace Tests\Unit;

use App\Enums\EstadoAlumno;
use App\Enums\EstadoPago;
use App\Models\Alumno;
use App\Models\Plan;
use App\Models\User;
use App\Services\PagoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PagoServiceTest extends TestCase
{
    use RefreshDatabase;

    private PagoService $service;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PagoService();
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    private function alumno(?Carbon $fechaExpiracion = null): Alumno
    {
        $padre = User::factory()->create(['rol' => 'padre']);

        $alumno = Alumno::create([
            'padre_id' => $padre->id,
            'nombre_completo' => 'Alumno de prueba',
            'fecha_nacimiento' => now()->subYears(10),
        ]);

        // fecha_expiracion no es mass-assignable (solo la toca PagoService),
        // así que para simular un alumno con historial de pago la forzamos aquí.
        if ($fechaExpiracion) {
            $alumno->forceFill(['fecha_expiracion' => $fechaExpiracion])->save();
        }

        return $alumno;
    }

    private function planMensual(int $dias = 30): Plan
    {
        return Plan::create([
            'nombre' => 'Mensual',
            'tipo' => 'mensual',
            'duracion_dias' => $dias,
            'precio' => 150,
            'moneda' => 'PEN',
        ]);
    }

    #[Test]
    public function alumno_sin_pago_previo_cuenta_la_vigencia_desde_hoy(): void
    {
        $alumno = $this->alumno(fechaExpiracion: null);
        $plan = $this->planMensual(30);

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        $this->assertTrue($pago->fecha_expiracion->isSameDay(now()->addDays(30)));
    }

    #[Test]
    public function alumno_al_dia_suma_dias_desde_su_expiracion_anterior(): void
    {
        $expiracionAnterior = now()->addDays(10); // aún vigente
        $alumno = $this->alumno($expiracionAnterior);
        $plan = $this->planMensual(30);

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        // 10 días que le quedaban + 30 del nuevo plan = 40 desde hoy
        $this->assertTrue($pago->fecha_expiracion->isSameDay(now()->addDays(40)));
    }

    #[Test]
    public function alumno_moroso_no_recupera_los_dias_vencidos(): void
    {
        $alumno = $this->alumno(now()->subDays(15)); // venció hace 15 días
        $plan = $this->planMensual(30);

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        // Se cuenta desde HOY, no desde la fecha vencida.
        $this->assertTrue($pago->fecha_expiracion->isSameDay(now()->addDays(30)));
    }

    #[Test]
    public function el_dia_exacto_de_expiracion_cuenta_como_al_dia(): void
    {
        $alumno = $this->alumno(Carbon::today()); // vence hoy mismo
        $plan = $this->planMensual(30);

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        $this->assertTrue($pago->fecha_expiracion->isSameDay(now()->addDays(30)));
    }

    #[Test]
    public function un_dia_despues_de_vencer_ya_se_considera_moroso(): void
    {
        $alumno = $this->alumno(now()->subDay());
        $plan = $this->planMensual(30);

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        $this->assertTrue($pago->fecha_expiracion->isSameDay(now()->addDays(30)));
    }

    #[Test]
    public function pago_confirmado_actualiza_el_alumno_a_activo_y_le_copia_la_expiracion(): void
    {
        $alumno = $this->alumno(null);
        $plan = $this->planMensual(30);

        $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);
        $alumno->refresh();

        $this->assertEquals(EstadoAlumno::Activo, $alumno->estado);
        $this->assertTrue($alumno->fecha_expiracion->isSameDay(now()->addDays(30)));
    }

    #[Test]
    public function pago_pendiente_no_actualiza_la_vigencia_del_alumno(): void
    {
        $alumno = $this->alumno(null);
        $plan = $this->planMensual(30);

        $this->service->registrarPago($alumno, $plan, $this->admin, [
            'metodo_pago' => 'efectivo',
            'estado' => 'pendiente',
        ]);
        $alumno->refresh();

        $this->assertNull($alumno->fecha_expiracion);
        $this->assertEquals(EstadoAlumno::SinPago, $alumno->estado);
    }

    #[Test]
    public function un_plan_pack_otorga_sesiones_en_vez_de_fecha_de_expiracion(): void
    {
        $alumno = $this->alumno(null);
        $plan = Plan::create([
            'nombre' => 'Pack 10 Clases',
            'tipo' => 'pack',
            'sesiones_max' => 10,
            'precio' => 120,
            'moneda' => 'PEN',
        ]);

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);
        $alumno->refresh();

        $this->assertNull($pago->fecha_expiracion);
        $this->assertEquals(10, $pago->sesiones_otorgadas);
        $this->assertEquals(10, $alumno->sesiones_restantes);
    }

    #[Test]
    public function comprar_un_segundo_pack_acumula_las_sesiones_restantes(): void
    {
        $alumno = $this->alumno(null);
        $plan = Plan::create([
            'nombre' => 'Pack 10 Clases',
            'tipo' => 'pack',
            'sesiones_max' => 10,
            'precio' => 120,
            'moneda' => 'PEN',
        ]);

        $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);
        $alumno->refresh();
        $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);
        $alumno->refresh();

        $this->assertEquals(20, $alumno->sesiones_restantes);
    }

    #[Test]
    public function el_monto_por_defecto_es_el_precio_del_plan_si_no_se_especifica(): void
    {
        $alumno = $this->alumno(null);
        $plan = $this->planMensual();

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        $this->assertEquals(150, $pago->monto);
    }

    #[Test]
    public function se_puede_cobrar_un_monto_distinto_al_precio_del_plan(): void
    {
        $alumno = $this->alumno(null);
        $plan = $this->planMensual();

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, [
            'metodo_pago' => 'efectivo',
            'monto' => 100,
        ]);

        $this->assertEquals(100, $pago->monto);
    }

    #[Test]
    public function cada_pago_genera_un_registro_de_auditoria(): void
    {
        $alumno = $this->alumno(null);
        $plan = $this->planMensual();

        $pago = $this->service->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        $this->assertDatabaseHas('auditoria_pagos', [
            'pago_id' => $pago->id,
            'accion' => 'creado',
        ]);
        $this->assertDatabaseHas('auditoria_pagos', [
            'pago_id' => $pago->id,
            'accion' => 'confirmado',
        ]);
    }
}
