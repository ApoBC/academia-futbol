@extends('layouts.app')

@section('title', 'Detalle de pago')

@section('content')
    <p class="mb-4"><a href="{{ route('pagos.index') }}">&larr; Volver a Pagos</a></p>

    <div class="max-w-xl rounded-xl border border-slate-200 bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-slate-800">Pago #{{ $pago->id }}</h1>
            @include('pagos._badge_estado', ['estado' => $pago->estado])
        </div>

        <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-400">Alumno</dt><dd class="text-slate-700">{{ $pago->alumno->nombre_completo }}</dd></div>
            <div><dt class="text-slate-400">Plan</dt><dd class="text-slate-700">{{ $pago->plan->nombre }}</dd></div>
            <div><dt class="text-slate-400">Monto</dt><dd class="font-medium text-slate-800">{{ $pago->moneda }} {{ number_format($pago->monto, 2) }}</dd></div>
            <div><dt class="text-slate-400">Método</dt><dd class="capitalize text-slate-700">{{ $pago->metodo_pago->value }}</dd></div>
            <div><dt class="text-slate-400">N° operación</dt><dd class="text-slate-700">{{ $pago->numero_operacion ?? '—' }}</dd></div>
            <div><dt class="text-slate-400">Fecha de pago</dt><dd class="text-slate-700">{{ $pago->fecha_pago->format('d/m/Y') }}</dd></div>
            <div><dt class="text-slate-400">Vigencia</dt><dd class="text-slate-700">{{ $pago->fecha_expiracion?->format('d/m/Y') ?? ($pago->sesiones_otorgadas . ' sesiones otorgadas') }}</dd></div>
            <div><dt class="text-slate-400">Registrado por</dt><dd class="text-slate-700">{{ $pago->admin->nombre }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-400">Notas</dt><dd class="text-slate-700">{{ $pago->notas ?? '—' }}</dd></div>
        </dl>
    </div>
@endsection
