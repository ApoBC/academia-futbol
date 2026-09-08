<?php

namespace App\Enums;

enum MetodoPago: string
{
    case Efectivo = 'efectivo';
    case Transferencia = 'transferencia';
    case Tarjeta = 'tarjeta';
    case Yape = 'yape';
    case Plin = 'plin';
    case Otro = 'otro';
}
