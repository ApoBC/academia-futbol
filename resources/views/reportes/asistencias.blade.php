@extends('layouts.app')

@section('title', 'Asistencias')

@section('content')
    <h1>Asistencias por período</h1>
    <p><a href="{{ route('reportes.dashboard') }}">&larr; Dashboard</a></p>

    <form method="GET">
        <label>Desde <input type="date" name="desde" value="{{ request('desde', $desde->format('Y-m-d')) }}"></label>
        <label>Hasta <input type="date" name="hasta" value="{{ request('hasta', $hasta->format('Y-m-d')) }}"></label>
        <button type="submit">Filtrar</button>
    </form>

    <p>{{ $asistencias->count() }} asistencias entre {{ $desde->format('d/m/Y') }} y {{ $hasta->format('d/m/Y') }}
        · <a href="{{ route('reportes.asistencias.export', request()->query()) }}">⬇ Exportar CSV</a></p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Alumno</th>
                <th>Categoría</th>
                <th>Profesor</th>
                <th>Origen</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($asistencias as $asistencia)
                <tr>
                    <td>{{ $asistencia->fecha->format('d/m/Y') }}</td>
                    <td>{{ $asistencia->hora }}</td>
                    <td>{{ $asistencia->alumno->nombre_completo }}</td>
                    <td>{{ $asistencia->alumno->categoria?->label() }}</td>
                    <td>{{ $asistencia->profesor->nombre }}</td>
                    <td>{{ $asistencia->origen->value }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Sin asistencias en este período.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
