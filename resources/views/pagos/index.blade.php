@extends('layouts.app')

@section('title', 'Pagos')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Pagos</h1>
        @can('create', App\Models\Pago::class)
            <a href="{{ route('pagos.create') }}" class="rounded-lg bg-brand-500 px-4 py-1.5 text-sm font-medium text-white hover:bg-brand-600">+ Registrar pago</a>
        @endcan
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table>
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Plan</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Fecha pago</th>
                    <th>Vence / Sesiones</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pagos as $pago)
                    <tr>
                        <td>{{ $pago->alumno->nombre_completo }}</td>
                        <td>{{ $pago->plan->nombre }}</td>
                        <td class="font-medium text-slate-700">{{ $pago->moneda }} {{ number_format($pago->monto, 2) }}</td>
                        <td class="capitalize">{{ $pago->metodo_pago->value }}</td>
                        <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                        <td>{{ $pago->fecha_expiracion?->format('d/m/Y') ?? ($pago->sesiones_otorgadas . ' sesiones') }}</td>
                        <td>@include('pagos._badge_estado', ['estado' => $pago->estado])</td>
                        <td><a href="{{ route('pagos.show', $pago) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-slate-400">Sin pagos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $pagos->links() }}</div>
@endsection
