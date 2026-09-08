@extends('layouts.app')

@section('title', 'Asistencias de hoy')

@section('content')
    <h1>Asistencias de hoy ({{ now()->format('d/m/Y') }})</h1>
    <p><a href="{{ route('escaneo.index') }}">&larr; Volver a escanear</a></p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Alumno</th>
                <th>Categoría</th>
                <th>Origen</th>
                <th>Registrado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($asistencias as $asistencia)
                <tr>
                    <td>{{ $asistencia->hora }}</td>
                    <td>{{ $asistencia->alumno->nombre_completo }}</td>
                    <td>{{ $asistencia->alumno->categoria?->label() }}</td>
                    <td>{{ $asistencia->origen->value }}</td>
                    <td>{{ $asistencia->profesor->nombre }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin asistencias registradas hoy.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
