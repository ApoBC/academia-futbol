<?php

namespace App\Enums;

enum MotivoFalloEscaneo: string
{
    case UuidNoEncontrado = 'uuid_no_encontrado';
    case DesencriptacionFallida = 'desencriptacion_fallida';
    case AlumnoBaja = 'alumno_baja';
    case QrExpirado = 'qr_expirado';
}
