<?php

namespace App\Enums;

enum EstadoAlumno: string
{
    case SinPago = 'sin_pago';
    case Activo = 'activo';
    case Suspendido = 'suspendido';
    case Baja = 'baja';
}
