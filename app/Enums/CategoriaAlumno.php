<?php

namespace App\Enums;

enum CategoriaAlumno: string
{
    case PreBenjamin = 'pre_benjamin';
    case Benjamin = 'benjamin';
    case Alevin = 'alevin';
    case Infantil = 'infantil';
    case Cadete = 'cadete';
    case Juvenil = 'juvenil';

    public function label(): string
    {
        return match ($this) {
            self::PreBenjamin => 'Pre-Benjamín',
            self::Benjamin => 'Benjamín',
            self::Alevin => 'Alevín',
            self::Infantil => 'Infantil',
            self::Cadete => 'Cadete',
            self::Juvenil => 'Juvenil',
        };
    }
}
