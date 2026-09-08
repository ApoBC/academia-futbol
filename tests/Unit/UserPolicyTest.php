<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserPolicy;
    }

    #[Test]
    public function un_admin_puede_crear_usuarios(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->assertTrue($this->policy->create($admin));
    }

    #[Test]
    public function un_profesor_no_puede_gestionar_usuarios(): void
    {
        $profesor = User::factory()->create(['rol' => 'profesor']);

        $this->assertFalse($this->policy->viewAny($profesor));
        $this->assertFalse($this->policy->create($profesor));
    }

    #[Test]
    public function un_admin_puede_editar_a_un_profesor(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $profesor = User::factory()->create(['rol' => 'profesor']);

        $this->assertTrue($this->policy->update($admin, $profesor));
    }

    #[Test]
    public function un_admin_no_puede_editar_a_otro_admin(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $otroAdmin = User::factory()->create(['rol' => 'admin']);

        $this->assertFalse($this->policy->update($admin, $otroAdmin));
    }

    #[Test]
    public function un_admin_no_puede_editar_a_un_superadmin(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $this->assertFalse($this->policy->update($admin, $superadmin));
    }

    #[Test]
    public function un_superadmin_puede_editar_a_cualquiera(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $admin = User::factory()->create(['rol' => 'admin']);
        $profesor = User::factory()->create(['rol' => 'profesor']);

        $this->assertTrue($this->policy->update($superadmin, $admin));
        $this->assertTrue($this->policy->update($superadmin, $profesor));
    }

    #[Test]
    public function nadie_puede_eliminarse_a_si_mismo(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $this->assertFalse($this->policy->delete($superadmin, $superadmin));
    }

    #[Test]
    public function un_admin_no_puede_eliminar_a_un_padre(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $padre = User::factory()->create(['rol' => 'padre']);

        $this->assertFalse($this->policy->delete($admin, $padre));
    }
}
