<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Autorización: solo admin. Aplicada en routes/web.php con middleware('role:admin'),
// igual que el resto de rutas restringidas por rol en este proyecto.
class ReporteController extends Controller
{
    public function __construct(private readonly ReporteService $reporteService) {}

    public function dashboard(): View
    {
        return view('reportes.dashboard', [
            'kpis' => $this->reporteService->kpis(),
            'ingresosPorMes' => $this->reporteService->ingresosPorMes(),
            'asistenciasPorCategoria' => $this->reporteService->asistenciasPorCategoria(),
            'pagosRecientes' => $this->reporteService->pagosRecientes(),
            'asistenciasHoy' => Asistencia::whereDate('fecha', now())->count(),
        ]);
    }

    public function deudores(Request $request): View
    {
        $deudores = $this->reporteService->deudores($request->query('categoria'));

        return view('reportes.deudores', compact('deudores'));
    }

    public function deudoresExport(Request $request): StreamedResponse
    {
        $deudores = $this->reporteService->deudores($request->query('categoria'));

        return $this->csv('deudores.csv', ['Alumno', 'Categoría', 'Días de mora', 'Última asistencia', 'Padre/Tutor', 'Teléfono'], $deudores->map(fn ($a) => [
            $a->nombre_completo,
            $a->categoria?->label(),
            $a->dias_mora,
            $a->ultima_asistencia ?? '—',
            $a->padre->nombre,
            $a->padre->telefono ?? '—',
        ]));
    }

    public function asistencias(Request $request): View
    {
        [$desde, $hasta] = $this->rangoFechas($request);
        $asistencias = $this->reporteService->asistenciasPorPeriodo($desde, $hasta, $request->query('categoria'));

        return view('reportes.asistencias', compact('asistencias', 'desde', 'hasta'));
    }

    public function asistenciasExport(Request $request): StreamedResponse
    {
        [$desde, $hasta] = $this->rangoFechas($request);
        $asistencias = $this->reporteService->asistenciasPorPeriodo($desde, $hasta, $request->query('categoria'));

        return $this->csv('asistencias.csv', ['Fecha', 'Hora', 'Alumno', 'Categoría', 'Profesor', 'Origen'], $asistencias->map(fn ($a) => [
            $a->fecha->format('Y-m-d'),
            $a->hora,
            $a->alumno->nombre_completo,
            $a->alumno->categoria?->label(),
            $a->profesor->nombre,
            $a->origen->value,
        ]));
    }

    public function ingresos(Request $request): View
    {
        [$desde, $hasta] = $this->rangoFechas($request, defaultDesde: now()->startOfMonth(), defaultHasta: now()->endOfMonth());
        $ingresos = $this->reporteService->ingresosPorMetodo($desde, $hasta);

        return view('reportes.ingresos', compact('ingresos', 'desde', 'hasta'));
    }

    public function ingresosExport(Request $request): StreamedResponse
    {
        [$desde, $hasta] = $this->rangoFechas($request, defaultDesde: now()->startOfMonth(), defaultHasta: now()->endOfMonth());
        $ingresos = $this->reporteService->ingresosPorMetodo($desde, $hasta);

        return $this->csv('ingresos.csv', ['Método de pago', 'Cantidad de pagos', 'Total'], $ingresos->map(fn ($i) => [
            $i->metodo_pago,
            $i->cantidad,
            number_format((float) $i->total, 2, '.', ''),
        ]));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rangoFechas(Request $request, ?Carbon $defaultDesde = null, ?Carbon $defaultHasta = null): array
    {
        $desde = $request->query('desde') ? Carbon::parse($request->query('desde')) : ($defaultDesde ?? now()->subDays(30));
        $hasta = $request->query('hasta') ? Carbon::parse($request->query('hasta')) : ($defaultHasta ?? now());

        return [$desde->startOfDay(), $hasta->endOfDay()];
    }

    /**
     * @param  array<int, string>  $encabezados
     * @param  Collection<int, array<int, mixed>>  $filas
     */
    private function csv(string $nombreArchivo, array $encabezados, Collection $filas): StreamedResponse
    {
        return response()->streamDownload(function () use ($encabezados, $filas) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8: acentos correctos al abrir en Excel
            fputcsv($handle, $encabezados);
            foreach ($filas as $fila) {
                fputcsv($handle, $fila);
            }
            fclose($handle);
        }, $nombreArchivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
