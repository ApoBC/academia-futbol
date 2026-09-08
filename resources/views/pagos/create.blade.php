@extends('layouts.app')

@section('title', 'Registrar pago')

@section('content')
    <h1>Registrar pago</h1>
    <form method="POST" action="{{ route('pagos.store') }}">
        @csrf

        <label>Alumno
            <select name="alumno_id" required>
                <option value="">-- Seleccionar --</option>
                @foreach ($alumnos as $alumno)
                    <option value="{{ $alumno->id }}" @selected(old('alumno_id') == $alumno->id)>
                        {{ $alumno->nombre_completo }} (vence: {{ $alumno->fecha_expiracion?->format('d/m/Y') ?? 'sin pago previo' }})
                    </option>
                @endforeach
            </select>
        </label><br>

        <label>Plan
            <select name="plan_id" required>
                <option value="">-- Seleccionar --</option>
                @foreach ($planes as $plan)
                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                        {{ $plan->nombre }} ({{ $plan->moneda }} {{ number_format($plan->precio, 2) }})
                    </option>
                @endforeach
            </select>
        </label><br>

        <label>Monto cobrado (dejar vacío para usar el precio del plan)
            <input type="number" step="0.01" name="monto" value="{{ old('monto') }}">
        </label><br>

        <label>Método de pago
            <select name="metodo_pago" required>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="yape">Yape</option>
                <option value="plin">Plin</option>
                <option value="otro">Otro</option>
            </select>
        </label><br>

        <label>N° de operación (opcional)
            <input type="text" name="numero_operacion" value="{{ old('numero_operacion') }}">
        </label><br>

        <label>Fecha de pago (vacío = hoy)
            <input type="date" name="fecha_pago" value="{{ old('fecha_pago') }}">
        </label><br>

        <label>Estado
            <select name="estado">
                <option value="confirmado">Confirmado (activa la vigencia)</option>
                <option value="pendiente">Pendiente</option>
            </select>
        </label><br>

        <label>Notas
            <textarea name="notas">{{ old('notas') }}</textarea>
        </label><br>

        <button type="submit">Registrar</button>
    </form>
@endsection
