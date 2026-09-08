<?php

namespace App\DataTransferObjects;

use App\Enums\MotivoFalloEscaneo;
use App\Enums\Semaforo;
use App\Models\Alumno;

class EscaneoResultado
{
    public function __construct(
        public readonly bool $exitoso,
        public readonly ?Alumno $alumno = null,
        public readonly ?Semaforo $semaforo = null,
        public readonly bool $yaRegistradoHoy = false,
        public readonly ?MotivoFalloEscaneo $motivoFallo = null,
    ) {}

    public static function fallo(MotivoFalloEscaneo $motivo): self
    {
        return new self(exitoso: false, motivoFallo: $motivo);
    }

    public static function exito(Alumno $alumno, Semaforo $semaforo, bool $yaRegistradoHoy): self
    {
        return new self(exitoso: true, alumno: $alumno, semaforo: $semaforo, yaRegistradoHoy: $yaRegistradoHoy);
    }
}
