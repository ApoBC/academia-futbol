<?php

namespace App\Enums;

enum AccionAuditoria: string
{
    case Creado = 'creado';
    case Confirmado = 'confirmado';
    case Editado = 'editado';
    case Anulado = 'anulado';
}
