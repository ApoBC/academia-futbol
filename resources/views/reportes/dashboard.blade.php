@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
    <h1>Dashboard</h1>

    <ul>
        <li>Alumnos activos: <strong>{{ $kpis['alumnos_activos'] }}</strong> de {{ $kpis['total_alumnos'] }}</li>
        <li>Tasa de morosidad: <strong>{{ $kpis['tasa_morosidad'] }}%</strong></li>
        <li>Ingresos del mes: <strong>S/ {{ number_format($kpis['ingresos_mes'], 2) }}</strong></li>
    </ul>

    <p>
        <a href="{{ route('reportes.deudores') }}">Ver deudores</a> ·
        <a href="{{ route('reportes.asistencias') }}">Ver asistencias</a> ·
        <a href="{{ route('reportes.ingresos') }}">Ver ingresos</a>
    </p>
@endsection
