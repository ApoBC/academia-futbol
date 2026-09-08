@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <h1 class="text-lg font-semibold text-slate-800">Bienvenido, {{ auth()->user()->nombre }} 👋</h1>
        <p class="mt-1 text-sm text-slate-500">Rol: {{ auth()->user()->rol->label() }}</p>

        <div class="mt-5 flex flex-wrap gap-3">
            @role('profesor')
                <a href="{{ route('escaneo.index') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">📷 Escanear carnés</a>
                <a href="{{ route('asistencias.hoy') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">📋 Asistencias de hoy</a>
            @endrole
            @role('padre')
                <a href="{{ route('alumnos.index') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">🧑‍🎓 Mis hijos</a>
                <a href="{{ route('pagos.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">💳 Mis pagos</a>
            @endrole
        </div>
    </div>
@endsection
