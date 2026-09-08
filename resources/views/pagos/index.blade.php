@extends('layouts.app')

@section('title', 'Pagos')

@section('content')
    <h1>Pagos</h1>

    @can('create', App\Models\Pago::class)
        <p><a href="{{ route('pagos.create') }}">+ Registrar pago</a></p>
    @endcan

    <table border="1" cellpadding="6">
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
                    <td>{{ $pago->moneda }} {{ number_format($pago->monto, 2) }}</td>
                    <td>{{ $pago->metodo_pago->value }}</td>
                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                    <td>{{ $pago->fecha_expiracion?->format('d/m/Y') ?? ($pago->sesiones_otorgadas . ' sesiones') }}</td>
                    <td>{{ $pago->estado->value }}</td>
                    <td><a href="{{ route('pagos.show', $pago) }}">Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="8">Sin pagos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $pagos->links() }}
@endsection
