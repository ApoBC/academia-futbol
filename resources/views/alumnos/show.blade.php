@extends('layouts.app')

@section('title', $alumno->nombre_completo)

@section('content')
    <h1>{{ $alumno->nombre_completo }}</h1>
    <ul>
        <li>UUID: {{ $alumno->uuid }}</li>
        <li>DNI: {{ $alumno->dni ?? '—' }}</li>
        <li>Fecha de nacimiento: {{ $alumno->fecha_nacimiento->format('d/m/Y') }}</li>
        <li>Categoría: {{ $alumno->categoria?->label() }}</li>
        <li>Sexo: {{ $alumno->sexo ?? '—' }}</li>
        <li>Estado: {{ $alumno->estado->value }}</li>
        <li>Vence: {{ $alumno->fecha_expiracion?->format('d/m/Y') ?? 'Sin pago registrado' }}</li>
        <li>Padre/Tutor: {{ $alumno->padre->nombre }} ({{ $alumno->padre->email }})</li>
        @if ($alumno->estado_salud_alerta)
            <li><strong>⚠ Alerta de salud:</strong> {{ $alumno->alergias_enfermedades }}</li>
        @endif
    </ul>
    <p><a href="{{ route('alumnos.index') }}">&larr; Volver</a></p>
@endsection
