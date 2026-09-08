<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CarneController extends Controller
{
    /**
     * Descarga el carné del alumno en PDF. Se regenera en cada descarga
     * (fecha de expiración siempre actualizada), no se guarda en disco.
     */
    public function download(Alumno $alumno): Response
    {
        $this->authorize('view', $alumno);

        $carne = $alumno->carneActivo();

        abort_if($carne === null, 404, 'Este alumno aún no tiene un carné generado.');

        // No requiere la extensión imagick: bacon-qr-code renderiza SVG con PHP puro.
        $qrSvg = base64_encode(
            QrCode::format('svg')->size(160)->margin(0)->generate($carne->uuid_encriptado)
        );

        $pdf = Pdf::loadView('pdf.carne', [
            'alumno' => $alumno,
            'carne' => $carne,
            'qrSvg' => $qrSvg,
        ]);

        // Tamaño real de un DNI / tarjeta ID-1: 85.6mm x 54mm, en puntos (1mm ≈ 2.8346pt).
        $pdf->setPaper([0, 0, 242.65, 153.07], 'portrait');

        return $pdf->download("carne-{$alumno->uuid}.pdf");
    }
}
