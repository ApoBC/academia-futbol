<?php

namespace App\Policies;

use App\Models\Alumno;
use App\Models\User;

class AlumnoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin', 'profesor', 'padre']);
    }

    public function view(User $user, Alumno $alumno): bool
    {
        if ($user->esAdmin() || $user->hasRole('profesor')) {
            return true;
        }

        return $user->hasRole('padre') && $alumno->padre_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->esAdmin();
    }

    public function update(User $user, Alumno $alumno): bool
    {
        return $user->esAdmin();
    }

    public function delete(User $user, Alumno $alumno): bool
    {
        return $user->esAdmin();
    }
}
