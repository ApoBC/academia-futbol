<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['superadmin', 'admin', 'profesor', 'padre'] as $rol) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        }

        // Sincroniza el rol de Spatie con la columna "rol" de cada usuario ya existente.
        User::query()->each(function (User $user): void {
            if (! $user->hasRole($user->rol->value)) {
                $user->assignRole($user->rol->value);
            }
        });
    }
}
