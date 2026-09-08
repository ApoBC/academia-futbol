<?php

namespace App\Services;

use App\Enums\EstadoAlumno;
use App\Enums\EstadoPago;
use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReporteService
{
    /**
     * Réplica de la vista v_morosos (docs/Diseno_Base_Datos_Academia_Futbol.md):
     * alumnos vencidos que no están de baja ni sin pago (nunca pagaron).
     *
     * @return Collection<int, Alumno>
     */
    public function deudores(?string $categoria = null): Collection
    {
        return Alumno::with('padre')
            ->whereNotNull('fecha_expiracion')
            ->where('fecha_expiracion', '<', now()->startOfDay())
            ->whereNotIn('estado', [EstadoAlumno::Baja, EstadoAlumno::SinPago])
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->get()
            ->map(function (Alumno $alumno) {
                $alumno->dias_mora = (int) abs(now()->startOfDay()->diffInDays($alumno->fecha_expiracion));
                $alumno->ultima_asistencia = $alumno->asistencias()->max('fecha');

                return $alumno;
            })
            ->sortByDesc('dias_mora')
            ->values();
    }

    /**
     * Asistencias registradas en un rango de fechas (inclusive).
     *
     * @return Collection<int, Asistencia>
     */
    public function asistenciasPorPeriodo(Carbon $desde, Carbon $hasta, ?string $categoria = null): Collection
    {
        return Asistencia::with(['alumno', 'profesor'])
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($categoria, fn ($q) => $q->whereHas('alumno', fn ($qa) => $qa->where('categoria', $categoria)))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();
    }

    /**
     * Ingresos confirmados agrupados por método de pago, en un rango de fechas.
     *
     * @return Collection<int, object{metodo_pago: string, cantidad: int, total: float}>
     */
    public function ingresosPorMetodo(Carbon $desde, Carbon $hasta): Collection
    {
        return Pago::selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(monto) as total')
            ->where('estado', EstadoPago::Confirmado)
            ->whereBetween('fecha_pago', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('metodo_pago')
            ->get();
    }

    /**
     * KPIs para el dashboard del admin.
     *
     * @return array{alumnos_activos: int, total_alumnos: int, tasa_morosidad: float, ingresos_mes: float}
     */
    public function kpis(): array
    {
        $totalAlumnos = Alumno::where('estado', '!=', EstadoAlumno::Baja)->count();
        $activos = Alumno::where('estado', EstadoAlumno::Activo)->count();
        $morosos = $this->deudores()->count();

        return [
            'alumnos_activos' => $activos,
            'total_alumnos' => $totalAlumnos,
            'tasa_morosidad' => $totalAlumnos > 0 ? round(($morosos / $totalAlumnos) * 100, 1) : 0.0,
            'ingresos_mes' => (float) Pago::where('estado', EstadoPago::Confirmado)
                ->whereBetween('fecha_pago', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('monto'),
        ];
    }
}
