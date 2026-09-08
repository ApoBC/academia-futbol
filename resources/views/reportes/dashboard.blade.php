@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- KPIs --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Alumnos activos</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $kpis['alumnos_activos'] }}
                <span class="text-sm font-normal text-slate-400">/ {{ $kpis['total_alumnos'] }}</span></p>
            <a href="{{ route('alumnos.index') }}" class="mt-2 inline-block text-sm">Ver alumnos →</a>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Tasa de morosidad</p>
            <p class="mt-1 text-2xl font-semibold {{ $kpis['tasa_morosidad'] > 20 ? 'text-red-600' : 'text-slate-800' }}">
                {{ $kpis['tasa_morosidad'] }}%
            </p>
            <a href="{{ route('reportes.deudores') }}" class="mt-2 inline-block text-sm">Ver deudores →</a>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Ingresos del mes</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">S/ {{ number_format($kpis['ingresos_mes'], 2) }}</p>
            <a href="{{ route('reportes.ingresos') }}" class="mt-2 inline-block text-sm">Ver ingresos →</a>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Asistencias hoy</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $asistenciasHoy }}</p>
            <a href="{{ route('asistencias.hoy') }}" class="mt-2 inline-block text-sm">Ver lista de hoy →</a>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Ingresos (últimos 6 meses)</h2>
            <canvas id="chartIngresos" height="220"></canvas>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Asistencias por categoría (este mes)</h2>
            <canvas id="chartAsistencias" height="220"></canvas>
        </div>
    </div>

    {{-- Pagos recientes --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Pagos recientes</h2>
        <table>
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pagosRecientes as $pago)
                    <tr>
                        <td>{{ $pago->alumno->nombre_completo }}</td>
                        <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                        <td>S/ {{ number_format($pago->monto, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-slate-400">Sin pagos registrados todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('chartIngresos'), {
            type: 'line',
            data: {
                labels: @json($ingresosPorMes->pluck('mes')),
                datasets: [{
                    label: 'Ingresos (S/)',
                    data: @json($ingresosPorMes->pluck('total')),
                    borderColor: '#0a5c36',
                    backgroundColor: 'rgba(10,92,54,0.1)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: { plugins: { legend: { display: false } } },
        });

        new Chart(document.getElementById('chartAsistencias'), {
            type: 'bar',
            data: {
                labels: @json($asistenciasPorCategoria->pluck('categoria')->map(fn ($c) => \App\Enums\CategoriaAlumno::from($c)->label())),
                datasets: [{
                    label: 'Asistencias',
                    data: @json($asistenciasPorCategoria->pluck('total')),
                    backgroundColor: '#0a5c36',
                    borderRadius: 6,
                }],
            },
            options: { plugins: { legend: { display: false } } },
        });
    </script>
@endsection
