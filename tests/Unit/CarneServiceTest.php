<?php

namespace Tests\Unit;

use App\Models\Alumno;
use App\Models\Plan;
use App\Models\User;
use App\Services\PagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarneServiceTest extends TestCase
{
    use RefreshDatabase;

    private function planMensual(): Plan
    {
        return Plan::create([
            'nombre' => 'Mensual',
            'tipo' => 'mensual',
            'duracion_dias' => 30,
            'precio' => 150,
            'moneda' => 'PEN',
        ]);
    }

    private function alumno(): Alumno
    {
        $padre = User::factory()->create(['rol' => 'padre']);

        return Alumno::create([
            'padre_id' => $padre->id,
            'nombre_completo' => 'Alumno de prueba',
            'fecha_nacimiento' => now()->subYears(10),
        ]);
    }

    #[Test]
    public function confirmar_un_pago_de_plan_con_vigencia_genera_el_carne_automaticamente(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $alumno = $this->alumno();
        $plan = $this->planMensual();

        $pago = app(PagoService::class)->registrarPago($alumno, $plan, $admin, ['metodo_pago' => 'efectivo']);

        $carne = $alumno->fresh()->carneActivo();

        $this->assertNotNull($carne);
        $this->assertEquals($pago->id, $carne->pago_id);
        $this->assertEquals(1, $carne->version);
        $this->assertTrue($carne->activo);
        $this->assertEquals($alumno->uuid, Crypt::decryptString($carne->uuid_encriptado));
    }

    #[Test]
    public function un_plan_pack_no_genera_carne_porque_no_tiene_fecha_de_vigencia(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $alumno = $this->alumno();
        $plan = Plan::create([
            'nombre' => 'Pack 10 Clases',
            'tipo' => 'pack',
            'sesiones_max' => 10,
            'precio' => 120,
            'moneda' => 'PEN',
        ]);

        app(PagoService::class)->registrarPago($alumno, $plan, $admin, ['metodo_pago' => 'efectivo']);

        $this->assertNull($alumno->fresh()->carneActivo());
    }

    #[Test]
    public function un_segundo_pago_confirmado_desactiva_el_carne_anterior_y_sube_de_version(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $alumno = $this->alumno();
        $plan = $this->planMensual();

        app(PagoService::class)->registrarPago($alumno, $plan, $admin, ['metodo_pago' => 'efectivo']);
        $primerCarne = $alumno->fresh()->carneActivo();

        app(PagoService::class)->registrarPago($alumno->fresh(), $plan, $admin, ['metodo_pago' => 'efectivo']);
        $alumno->refresh();

        $this->assertEquals(2, $alumno->carnes()->count());
        $this->assertFalse($primerCarne->fresh()->activo);
        $this->assertEquals(2, $alumno->carneActivo()->version);
    }
}
