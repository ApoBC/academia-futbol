<?php

namespace Tests\Unit;

use App\Models\Alumno;
use App\Models\ConfiguracionOffline;
use App\Models\Plan;
use App\Models\User;
use App\Services\PagoService;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private SyncService $service;

    private User $profesor;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SyncService::class);
        $this->profesor = User::factory()->create(['rol' => 'profesor']);
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    private function alumnoConCarne(): Alumno
    {
        $padre = User::factory()->create(['rol' => 'padre']);
        $alumno = Alumno::create([
            'padre_id' => $padre->id,
            'nombre_completo' => 'Alumno con carné',
            'fecha_nacimiento' => now()->subYears(10),
        ]);
        $plan = Plan::create(['nombre' => 'Mensual', 'tipo' => 'mensual', 'duracion_dias' => 30, 'precio' => 150, 'moneda' => 'PEN']);
        app(PagoService::class)->registrarPago($alumno, $plan, $this->admin, ['metodo_pago' => 'efectivo']);

        return $alumno->fresh();
    }

    #[Test]
    public function el_paquete_de_descarga_solo_incluye_alumnos_con_carne_activo(): void
    {
        $conCarne = $this->alumnoConCarne();
        $sinCarne = Alumno::create([
            'padre_id' => User::factory()->create(['rol' => 'padre'])->id,
            'nombre_completo' => 'Sin carné',
            'fecha_nacimiento' => now()->subYears(9),
        ]);

        $paquete = $this->service->paqueteDescarga($this->profesor);

        $this->assertCount(1, $paquete);
        $this->assertEquals('Alumno con carné', $paquete[0]['nombre_completo']);
        $this->assertEquals('VERDE', $paquete[0]['semaforo']);
        $this->assertEquals($conCarne->carneActivo()->uuid_encriptado, $paquete[0]['contenido_qr']);
    }

    #[Test]
    public function descargar_el_paquete_registra_la_configuracion_offline_del_profesor(): void
    {
        $this->alumnoConCarne();

        $this->service->paqueteDescarga($this->profesor);

        $this->assertDatabaseHas('configuracion_offline', [
            'profesor_id' => $this->profesor->id,
            'cantidad_registros_cache' => 1,
        ]);
    }

    #[Test]
    public function procesar_la_cola_registra_las_asistencias_con_la_fecha_y_hora_offline_originales(): void
    {
        $alumno = $this->alumnoConCarne();
        $qr = $alumno->carneActivo()->uuid_encriptado;

        $resultados = $this->service->procesarCola(
            [['contenido_qr' => $qr, 'fecha' => '2026-01-05', 'hora' => '08:15:00']],
            $this->profesor,
            '192.168.1.10',
            'dispositivo-abc',
        );

        $this->assertTrue($resultados[0]['ok']);
        $this->assertDatabaseHas('asistencias', [
            'alumno_id' => $alumno->id,
            'hora' => '08:15:00',
            'dispositivo_sync' => 'dispositivo-abc',
            'sincronizado' => 1,
        ]);
        $this->assertTrue(
            $alumno->asistencias()->first()->fecha->isSameDay('2026-01-05')
        );
    }

    #[Test]
    public function procesar_la_cola_marca_ok_pero_ya_registrado_si_choca_con_una_asistencia_existente(): void
    {
        $alumno = $this->alumnoConCarne();
        $qr = $alumno->carneActivo()->uuid_encriptado;

        $items = [['contenido_qr' => $qr, 'fecha' => '2026-01-05', 'hora' => '08:15:00']];
        $this->service->procesarCola($items, $this->profesor, null, 'disp-1');
        $resultados = $this->service->procesarCola($items, $this->profesor, null, 'disp-1');

        $this->assertTrue($resultados[0]['ok']);
        $this->assertTrue($resultados[0]['ya_registrado']);
        $this->assertEquals(1, $alumno->asistencias()->count());
    }

    #[Test]
    public function procesar_la_cola_actualiza_la_configuracion_offline_y_limpia_pendientes(): void
    {
        $alumno = $this->alumnoConCarne();
        $qr = $alumno->carneActivo()->uuid_encriptado;

        ConfiguracionOffline::create(['profesor_id' => $this->profesor->id, 'asistencias_pendientes_sync' => 5]);

        $this->service->procesarCola(
            [['contenido_qr' => $qr, 'fecha' => now()->toDateString(), 'hora' => '09:00:00']],
            $this->profesor,
            null,
            null,
        );

        $this->assertDatabaseHas('configuracion_offline', [
            'profesor_id' => $this->profesor->id,
            'asistencias_pendientes_sync' => 0,
        ]);
    }

    #[Test]
    public function procesar_la_cola_reporta_fallo_para_un_qr_invalido_sin_romper_el_resto(): void
    {
        $alumno = $this->alumnoConCarne();
        $qrValido = $alumno->carneActivo()->uuid_encriptado;

        $resultados = $this->service->procesarCola([
            ['contenido_qr' => 'basura-no-encriptada', 'fecha' => now()->toDateString(), 'hora' => '10:00:00'],
            ['contenido_qr' => $qrValido, 'fecha' => now()->toDateString(), 'hora' => '10:01:00'],
        ], $this->profesor, null, null);

        $this->assertFalse($resultados[0]['ok']);
        $this->assertEquals('desencriptacion_fallida', $resultados[0]['motivo']);
        $this->assertTrue($resultados[1]['ok']);
    }
}
