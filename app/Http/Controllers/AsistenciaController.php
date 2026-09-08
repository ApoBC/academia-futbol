<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    /**
     * Lista de asistencias del día, visible para profesor y admin.
     */
    public function hoy(): View
    {
        $asistencias = Asistencia::with(['alumno', 'profesor'])
            ->whereDate('fecha', now()->toDateString())
            ->orderByDesc('hora')
            ->get();

        return view('asistencias.hoy', compact('asistencias'));
    }
}
