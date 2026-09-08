<?php

namespace Tests\Unit;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Plan;
use App\Models\User;
use App\Services\PagoService;
use App\Services\ReporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReporteService $service;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ReporteService::class);
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    private function alumno(array $overrides = []): Alumno
    {
        $padre = User::factory()->create(['rol' => 'padre']);
        $alumno = Alumno::create([
            'padre_id' => $padre->id,
            'nombre_completo' => 'Alumno de prueba',
            'fecha_nacimiento' => now()->subYears(10),
        ]);

        if ($overrides) {
            $alumno->forceFill($overrides)->save();
        }

        return $alumno;
    }

    #[Test]
    public function deudores_incluye_solo_alumnos_vencidos_que_no_estan_de_baja_ni_sin_pago(): void
    {
        $vencido = $this->alumno(['estado' => 'suspendido', 'fecha_expiracion' => now()->subDays(10)]);
        $this->alumno(['estado' => 'activo', 'fecha_expiracion' => now()->addDays(5)]); // al día
        $this->alumno(['estado' => 'baja', 'fecha_expiracion' => now()->subDays(30)]); // de baja
        $this->alumno(); // sin_pago, nunca pagó

        $deudores = $this->service->deudores();

        $this->assertCount(1, $deudores);
        $this->assertEquals($vencido->id, $deudores->first()->id);
    }

    #[Test]
    public function deudores_calcula_los_dias_de_mora_correctamente(): void
    {
        $this->alumno(['estado' => 'suspendido', 'fecha_expiracion' => now()->subDays(15)]);

        $deudores = $this->service->deudores();

        $this->assertEquals(15, $deudores->first()->dias_mora);
    }

    #[Test]
    public function deudores_filtra_por_categoria(): void
    {
        $this->alumno(['estado' => 'suspendido', 'fecha_expiracion' => now()->subDays(5), 'categoria' => 'sub_8']);
        $this->alumno(['estado' => 'suspendido', 'fecha_expiracion' => now()->subDays(5), 'categoria' => 'mayores']);

        $deudores = $this->service->deudores('sub_8');

        $this->assertCount(1, $deudores);
        $this->assertEquals('sub_8', $deudores->first()->categoria->value);
    }

    #[Test]
    public function asistencias_por_periodo_respeta_el_rango_de_fechas(): void
    {
        $alumno = $this->alumno();
        $profesor = User::factory()->create(['rol' => 'profesor']);

        Asistencia::create(['alumno_id' => $alumno->id, 'profesor_id' => $profesor->id, 'fecha' => now()->subDays(40), 'hora' => '10:00:00']);
        Asistencia::create(['alumno_id' => $alumno->id, 'profesor_id' => $profesor->id, 'fecha' => now()->subDays(5), 'hora' => '10:00:00']);

        $resultado = $this->service->asistenciasPorPeriodo(now()->subDays(10), now());

        $this->assertCount(1, $resultado);
    }

    #[Test]
    public function ingresos_por_metodo_solo_cuenta_pagos_confirmados_del_periodo(): void
    {
        $alumno = $this->alumno();
        $plan = Plan::create(['nombre' => 'Mensual', 'tipo' => 'mensual', 'duracion_dias' => 30, 'precio' => 150, 'moneda' => 'PEN']);

        app(PagoService::class)->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);
        app(PagoService::class)->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'yape', 'estado' => 'pendiente']);
        app(PagoService::class)->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        $ingresos = $this->service->ingresosPorMetodo(now()->startOfMonth(), now()->endOfMonth());

        $efectivo = $ingresos->firstWhere('metodo_pago', 'efectivo');
        $this->assertEquals(2, $efectivo->cantidad);
        $this->assertEquals(300.0, (float) $efectivo->total);
        $this->assertNull($ingresos->firstWhere('metodo_pago', 'yape'));
    }

    #[Test]
    public function kpis_calcula_la_tasa_de_morosidad_sobre_alumnos_no_dados_de_baja(): void
    {
        $this->alumno(['estado' => 'activo', 'fecha_expiracion' => now()->addDays(5)]);
        $this->alumno(['estado' => 'suspendido', 'fecha_expiracion' => now()->subDays(5)]);
        $this->alumno(['estado' => 'baja']);

        $kpis = $this->service->kpis();

        $this->assertEquals(2, $kpis['total_alumnos']); // baja excluido
        $this->assertEquals(1, $kpis['alumnos_activos']);
        $this->assertEquals(50.0, $kpis['tasa_morosidad']);
    }
}
