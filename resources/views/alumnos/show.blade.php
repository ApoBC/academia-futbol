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
    <h2>Historial de pagos</h2>
    <ul>
        @forelse ($alumno->pagos()->latest('fecha_pago')->get() as $pago)
            <li>
                <a href="{{ route('pagos.show', $pago) }}">
                    {{ $pago->fecha_pago->format('d/m/Y') }} — {{ $pago->plan->nombre }} —
                    {{ $pago->moneda }} {{ number_format($pago->monto, 2) }} ({{ $pago->estado->value }})
                </a>
            </li>
        @empty
            <li>Sin pagos registrados.</li>
        @endforelse
    </ul>

    @can('create', App\Models\Pago::class)
        <p><a href="{{ route('pagos.create') }}">+ Registrar pago para este alumno</a></p>
    @endcan

    <p><a href="{{ route('alumnos.index') }}">&larr; Volver</a></p>
@endsection
