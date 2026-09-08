<?php

namespace App\Enums;

enum RolUsuario: string
{
    case Admin = 'admin';
    case Profesor = 'profesor';
    case Padre = 'padre';
}
