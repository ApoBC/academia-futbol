<?php

namespace App\Models;

use App\Enums\RolUsuario;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['nombre', 'email', 'password', 'telefono', 'documento_identidad', 'rol', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Este proyecto usa "usuarios" en vez de la tabla "users" por defecto de Laravel.
     * Ver docs/Diseno_Base_Datos_Academia_Futbol.md.
     */
    protected $table = 'usuarios';

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->uuid ??= (string) Str::uuid();
        });

        // La columna "rol" es la única fuente de verdad para el rol de un
        // usuario; el rol de Spatie se mantiene sincronizado automáticamente
        // aquí para que sea imposible olvidarse de llamar assignRole() en
        // algún punto de creación/edición y que ambos queden desincronizados.
        static::created(function (User $user): void {
            $user->sincronizarRolDeSpatie();
        });

        static::updated(function (User $user): void {
            if ($user->wasChanged('rol')) {
                $user->sincronizarRolDeSpatie();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => RolUsuario::class,
            'activo' => 'boolean',
        ];
    }

    /**
     * Hijos (alumnos) de este usuario, cuando su rol es "padre".
     */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'padre_id');
    }

    /**
     * Superadmin y admin comparten todos los permisos operativos (alumnos,
     * pagos, reportes, escaneo). Lo único exclusivo del superadmin es poder
     * gestionar cuentas de administrador (ver UserPolicy).
     */
    public function esAdmin(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Crea el rol de Spatie si todavía no existe (p. ej. en un entorno de
     * test que no corrió RoleSeeder) y lo asigna, para que "rol" nunca
     * quede desincronizado del permiso real de Spatie.
     */
    private function sincronizarRolDeSpatie(): void
    {
        Role::firstOrCreate(['name' => $this->rol->value, 'guard_name' => 'web']);
        $this->syncRoles([$this->rol->value]);
    }
}
