@php
    $estilos = [
        'activo' => 'bg-brand-50 text-brand-700',
        'sin_pago' => 'bg-amber-50 text-amber-700',
        'suspendido' => 'bg-red-50 text-red-700',
        'baja' => 'bg-slate-100 text-slate-500',
    ];
@endphp
<span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $estilos[$estado->value] ?? 'bg-slate-100 text-slate-600' }}">
    {{ ucfirst(str_replace('_', ' ', $estado->value)) }}
</span>
