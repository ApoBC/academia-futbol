<?php

namespace App\Enums;

enum TipoPlan: string
{
    case Mensual = 'mensual';
    case Pack = 'pack';
    case Trimestral = 'trimestral';
    case Anual = 'anual';
}
