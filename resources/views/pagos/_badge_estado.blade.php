@php
    $estilos = [
        'confirmado' => 'bg-brand-50 text-brand-700',
        'pendiente' => 'bg-amber-50 text-amber-700',
        'anulado' => 'bg-red-50 text-red-700',
    ];
@endphp
<span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $estilos[$estado->value] ?? 'bg-slate-100 text-slate-600' }}">
    {{ ucfirst($estado->value) }}
</span>
