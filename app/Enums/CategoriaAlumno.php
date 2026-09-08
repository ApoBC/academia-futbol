<?php

namespace App\Enums;

enum CategoriaAlumno: string
{
    case Sub8 = 'sub_8';
    case Sub10 = 'sub_10';
    case Sub12 = 'sub_12';
    case Sub14 = 'sub_14';
    case Sub17 = 'sub_17';
    case Mayores = 'mayores';

    public function label(): string
    {
        return match ($this) {
            self::Sub8 => 'Sub-8',
            self::Sub10 => 'Sub-10',
            self::Sub12 => 'Sub-12',
            self::Sub14 => 'Sub-14',
            self::Sub17 => 'Sub-17',
            self::Mayores => 'Mayores',
        };
    }
}
