<?php

namespace Tests\Unit;

use App\Enums\MotivoFalloEscaneo;
use App\Enums\Semaforo;
use App\Models\Alumno;
use App\Models\User;
use App\Services\EscaneoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EscaneoServiceTest extends TestCase
{
    use RefreshDatabase;

    private EscaneoService $service;

    private User $profesor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new EscaneoService;
        $this->profesor = User::factory()->create(['rol' => 'profesor']);
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
    public function un_qr_valido_de_un_alumno_al_dia_registra_asistencia_en_verde(): void
    {
        $alumno = $this->alumno(['estado' => 'activo', 'fecha_expiracion' => now()->addDays(10)]);
        $qr = Crypt::encryptString($alumno->uuid);

        $resultado = $this->service->escanear($qr, $this->profesor);

        $this->assertTrue($resultado->exitoso);
        $this->assertEquals(Semaforo::Verde, $resultado->semaforo);
        $this->assertFalse($resultado->yaRegistradoHoy);
        $this->assertDatabaseHas('asistencias', ['alumno_id' => $alumno->id, 'profesor_id' => $this->profesor->id]);
    }

    #[Test]
    public function escanear_dos_veces_el_mismo_dia_no_duplica_la_asistencia(): void
    {
        $alumno = $this->alumno(['estado' => 'activo', 'fecha_expiracion' => now()->addDays(10)]);
        $qr = Crypt::encryptString($alumno->uuid);

        $this->service->escanear($qr, $this->profesor);
        $segundo = $this->service->escanear($qr, $this->profesor);

        $this->assertTrue($segundo->exitoso);
        $this->assertTrue($segundo->yaRegistradoHoy);
        $this->assertEquals(1, $alumno->asistencias()->count());
    }

    #[Test]
    public function un_qr_con_contenido_basura_no_desencriptable_se_registra_como_fallo(): void
    {
        $resultado = $this->service->escanear('esto-no-es-un-qr-valido', $this->profesor);

        $this->assertFalse($resultado->exitoso);
        $this->assertEquals(MotivoFalloEscaneo::DesencriptacionFallida, $resultado->motivoFallo);
        $this->assertDatabaseHas('intentos_escaneo_fallidos', [
            'profesor_id' => $this->profesor->id,
            'motivo_fallo' => 'desencriptacion_fallida',
        ]);
    }

    #[Test]
    public function un_uuid_encriptado_que_no_pertenece_a_ningun_alumno_se_registra_como_fallo(): void
    {
        $qr = Crypt::encryptString((string) Str::uuid());

        $resultado = $this->service->escanear($qr, $this->profesor);

        $this->assertFalse($resultado->exitoso);
        $this->assertEquals(MotivoFalloEscaneo::UuidNoEncontrado, $resultado->motivoFallo);
    }

    #[Test]
    public function un_alumno_de_baja_no_puede_escanear_su_carne(): void
    {
        $alumno = $this->alumno(['estado' => 'baja']);
        $qr = Crypt::encryptString($alumno->uuid);

        $resultado = $this->service->escanear($qr, $this->profesor);

        $this->assertFalse($resultado->exitoso);
        $this->assertEquals(MotivoFalloEscaneo::AlumnoBaja, $resultado->motivoFallo);
        $this->assertDatabaseMissing('asistencias', ['alumno_id' => $alumno->id]);
    }

    #[Test]
    public function un_alumno_sin_pago_da_semaforo_rojo_pero_igual_registra_asistencia(): void
    {
        $alumno = $this->alumno(); // estado por defecto: sin_pago
        $qr = Crypt::encryptString($alumno->uuid);

        $resultado = $this->service->escanear($qr, $this->profesor);

        $this->assertTrue($resultado->exitoso);
        $this->assertEquals(Semaforo::Rojo, $resultado->semaforo);
    }

    #[Test]
    public function un_alumno_a_menos_de_7_dias_de_vencer_da_semaforo_ambar(): void
    {
        $alumno = $this->alumno(['estado' => 'activo', 'fecha_expiracion' => now()->subDays(3)]);

        $this->assertEquals(Semaforo::Ambar, $this->service->calcularSemaforo($alumno));
    }

    #[Test]
    public function un_alumno_vencido_hace_mas_de_7_dias_da_semaforo_rojo(): void
    {
        $alumno = $this->alumno(['estado' => 'suspendido', 'fecha_expiracion' => now()->subDays(10)]);

        $this->assertEquals(Semaforo::Rojo, $this->service->calcularSemaforo($alumno));
    }

    #[Test]
    public function un_alumno_con_pack_y_sesiones_restantes_da_semaforo_verde(): void
    {
        $alumno = $this->alumno(['estado' => 'activo', 'sesiones_restantes' => 5]);

        $this->assertEquals(Semaforo::Verde, $this->service->calcularSemaforo($alumno));
    }

    #[Test]
    public function un_alumno_con_pack_sin_sesiones_restantes_da_semaforo_rojo(): void
    {
        $alumno = $this->alumno(['estado' => 'activo', 'sesiones_restantes' => 0]);

        $this->assertEquals(Semaforo::Rojo, $this->service->calcularSemaforo($alumno));
    }
}
