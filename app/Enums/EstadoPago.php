<?php

namespace App\Enums;

enum EstadoPago: string
{
    case Confirmado = 'confirmado';
    case Pendiente = 'pendiente';
    case Anulado = 'anulado';
}
