<?php

namespace App\Enums;

enum RolUsuario: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case Profesor = 'profesor';
    case Padre = 'padre';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Administrador',
            self::Profesor => 'Profesor',
            self::Padre => 'Padre/Tutor',
        };
    }
}
