<?php

namespace App\Policies;

use App\Models\Pago;
use App\Models\User;

class PagoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin', 'padre']);
    }

    public function view(User $user, Pago $pago): bool
    {
        if ($user->esAdmin()) {
            return true;
        }

        return $user->hasRole('padre') && $pago->alumno->padre_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->esAdmin();
    }

    public function update(User $user, Pago $pago): bool
    {
        return $user->esAdmin();
    }
}
