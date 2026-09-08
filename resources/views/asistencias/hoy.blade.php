@extends('layouts.app')

@section('title', 'Asistencias de hoy')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Asistencias de hoy ({{ now()->format('d/m/Y') }})</h1>
        <a href="{{ route('escaneo.index') }}" class="text-sm">&larr; Volver a escanear</a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table>
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
                        <td>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                                {{ $asistencia->origen->value === 'escaneo_qr' ? 'QR' : 'Manual' }}
                            </span>
                        </td>
                        <td>{{ $asistencia->profesor->nombre }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-400">Sin asistencias registradas hoy.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
