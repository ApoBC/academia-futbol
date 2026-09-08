@extends('layouts.app')

@section('title', $alumno->nombre_completo)

@section('content')
    <p class="mb-4"><a href="{{ route('alumnos.index') }}">&larr; Volver a Alumnos</a></p>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Ficha --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-2">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-lg font-semibold text-brand-700">
                        {{ mb_strtoupper(mb_substr($alumno->nombre_completo, 0, 1)) }}
                    </span>
                    <div>
                        <h1 class="text-lg font-semibold text-slate-800">{{ $alumno->nombre_completo }}</h1>
                        <p class="text-sm text-slate-500">{{ $alumno->categoria?->label() }}</p>
                    </div>
                </div>
                @include('alumnos._badge_estado', ['estado' => $alumno->estado])
            </div>

            @if ($alumno->estado_salud_alerta)
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
                    ⚠ Alerta de salud: {{ $alumno->alergias_enfermedades }}
                </div>
            @endif

            <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-400">DNI</dt><dd class="text-slate-700">{{ $alumno->dni ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Fecha de nacimiento</dt><dd class="text-slate-700">{{ $alumno->fecha_nacimiento->format('d/m/Y') }}</dd></div>
                <div><dt class="text-slate-400">Sexo</dt><dd class="text-slate-700">{{ $alumno->sexo ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Vence</dt><dd class="text-slate-700">{{ $alumno->fecha_expiracion?->format('d/m/Y') ?? 'Sin pago registrado' }}</dd></div>
                <div><dt class="text-slate-400">Padre/Tutor</dt><dd class="text-slate-700">{{ $alumno->padre->nombre }} ({{ $alumno->padre->email }})</dd></div>
                <div><dt class="text-slate-400">UUID</dt><dd class="break-all font-mono text-xs text-slate-400">{{ $alumno->uuid }}</dd></div>
            </dl>

            <div class="mt-5 flex gap-3">
                @if ($alumno->carneActivo())
                    <a href="{{ route('alumnos.carne', $alumno) }}" class="rounded-lg bg-brand-500 px-4 py-1.5 text-sm font-medium text-white hover:bg-brand-600">📄 Descargar carné QR</a>
                @else
                    <span class="rounded-lg bg-slate-100 px-4 py-1.5 text-sm text-slate-500">Sin carné (se genera al confirmar un pago)</span>
                @endif
                @can('update', $alumno)
                    <a href="{{ route('alumnos.edit', $alumno) }}" class="rounded-lg border border-slate-300 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Editar</a>
                @endcan
            </div>
        </div>

        {{-- Historial de pagos --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Historial de pagos</h2>
                @can('create', App\Models\Pago::class)
                    <a href="{{ route('pagos.create') }}" class="text-sm">+ Nuevo</a>
                @endcan
            </div>
            <ul class="space-y-2 text-sm">
                @forelse ($alumno->pagos()->latest('fecha_pago')->get() as $pago)
                    <li>
                        <a href="{{ route('pagos.show', $pago) }}" class="flex items-center justify-between rounded-lg px-2 py-1.5 hover:bg-slate-50">
                            <span class="text-slate-600">{{ $pago->fecha_pago->format('d/m/Y') }} · {{ $pago->plan->nombre }}</span>
                            <span class="font-medium text-slate-800">{{ $pago->moneda }} {{ number_format($pago->monto, 2) }}</span>
                        </a>
                    </li>
                @empty
                    <li class="text-slate-400">Sin pagos registrados.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
