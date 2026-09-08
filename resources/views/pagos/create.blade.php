@extends('layouts.app')

@section('title', 'Registrar pago')

@section('content')
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Registrar pago</h1>

    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <form method="POST" action="{{ route('pagos.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Alumno</label>
                    <select name="alumno_id" required class="w-full">
                        <option value="">-- Seleccionar --</option>
                        @foreach ($alumnos as $alumno)
                            <option value="{{ $alumno->id }}" @selected(old('alumno_id') == $alumno->id)>
                                {{ $alumno->nombre_completo }} (vence: {{ $alumno->fecha_expiracion?->format('d/m/Y') ?? 'sin pago previo' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Plan</label>
                    <select name="plan_id" required class="w-full">
                        <option value="">-- Seleccionar --</option>
                        @foreach ($planes as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                {{ $plan->nombre }} ({{ $plan->moneda }} {{ number_format($plan->precio, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Monto cobrado</label>
                    <input type="number" step="0.01" name="monto" value="{{ old('monto') }}" placeholder="Vacío = precio del plan" class="w-full">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Método de pago</label>
                    <select name="metodo_pago" required class="w-full">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">N° de operación (opcional)</label>
                    <input type="text" name="numero_operacion" value="{{ old('numero_operacion') }}" class="w-full">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Fecha de pago</label>
                    <input type="date" name="fecha_pago" value="{{ old('fecha_pago') }}" placeholder="Vacío = hoy" class="w-full">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Estado</label>
                    <select name="estado" class="w-full">
                        <option value="confirmado">Confirmado (activa la vigencia)</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Notas</label>
                    <textarea name="notas" rows="2" class="w-full">{{ old('notas') }}</textarea>
                </div>
            </div>

            <button type="submit" class="mt-5">Registrar</button>
        </form>
    </div>
@endsection
