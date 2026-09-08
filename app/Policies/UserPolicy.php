<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->esAdmin();
    }

    public function create(User $user): bool
    {
        return $user->esAdmin();
    }

    /**
     * Superadmin gestiona a cualquiera. Admin (no super) solo gestiona
     * cuentas de profesor: no puede tocar otros admins ni superadmins.
     */
    public function update(User $user, User $target): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->hasRole('admin') && $target->rol === RolUsuario::Profesor;
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false; // nadie se elimina a sí mismo desde este panel.
        }

        return $this->update($user, $target);
    }
}
