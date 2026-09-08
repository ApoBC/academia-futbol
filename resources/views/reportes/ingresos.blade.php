@extends('layouts.app')

@section('title', 'Ingresos')

@section('content')
    <h1>Ingresos por método de pago</h1>
    <p><a href="{{ route('reportes.dashboard') }}">&larr; Dashboard</a></p>

    <form method="GET">
        <label>Desde <input type="date" name="desde" value="{{ request('desde', $desde->format('Y-m-d')) }}"></label>
        <label>Hasta <input type="date" name="hasta" value="{{ request('hasta', $hasta->format('Y-m-d')) }}"></label>
        <button type="submit">Filtrar</button>
    </form>

    <p>Período: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}
        · <a href="{{ route('reportes.ingresos.export', request()->query()) }}">⬇ Exportar CSV</a></p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>Método de pago</th>
                <th>Cantidad de pagos</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ingresos as $ingreso)
                <tr>
                    <td>{{ $ingreso->metodo_pago }}</td>
                    <td>{{ $ingreso->cantidad }}</td>
                    <td>S/ {{ number_format($ingreso->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Sin ingresos confirmados en este período.</td></tr>
            @endforelse
        </tbody>
        @if ($ingresos->isNotEmpty())
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th>{{ $ingresos->sum('cantidad') }}</th>
                    <th>S/ {{ number_format($ingresos->sum('total'), 2) }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
