@extends('layouts.app')

@section('title', 'Detalle de pago')

@section('content')
    <h1>Pago #{{ $pago->id }}</h1>
    <ul>
        <li>Alumno: {{ $pago->alumno->nombre_completo }}</li>
        <li>Plan: {{ $pago->plan->nombre }}</li>
        <li>Monto: {{ $pago->moneda }} {{ number_format($pago->monto, 2) }}</li>
        <li>Método: {{ $pago->metodo_pago->value }}</li>
        <li>N° operación: {{ $pago->numero_operacion ?? '—' }}</li>
        <li>Fecha de pago: {{ $pago->fecha_pago->format('d/m/Y') }}</li>
        <li>Vigencia: {{ $pago->fecha_expiracion?->format('d/m/Y') ?? ($pago->sesiones_otorgadas . ' sesiones otorgadas') }}</li>
        <li>Estado: {{ $pago->estado->value }}</li>
        <li>Registrado por: {{ $pago->admin->nombre }}</li>
        <li>Notas: {{ $pago->notas ?? '—' }}</li>
    </ul>
    <p><a href="{{ route('pagos.index') }}">&larr; Volver</a></p>
@endsection
