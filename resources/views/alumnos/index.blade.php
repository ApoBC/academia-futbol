@extends('layouts.app')

@section('title', 'Alumnos')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Alumnos</h1>
        @can('create', App\Models\Alumno::class)
            <a href="{{ route('alumnos.create') }}" class="rounded-lg bg-brand-500 px-4 py-1.5 text-sm font-medium text-white hover:bg-brand-600">+ Nuevo alumno</a>
        @endcan
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table>
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Padre/Tutor</th>
                    <th>Expira</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alumnos as $alumno)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-700">
                                    {{ mb_strtoupper(mb_substr($alumno->nombre_completo, 0, 1)) }}
                                </span>
                                {{ $alumno->nombre_completo }}
                            </div>
                        </td>
                        <td>{{ $alumno->categoria?->label() }}</td>
                        <td>@include('alumnos._badge_estado', ['estado' => $alumno->estado])</td>
                        <td>{{ $alumno->padre->nombre }}</td>
                        <td>{{ $alumno->fecha_expiracion?->format('d/m/Y') ?? '—' }}</td>
                        <td class="space-x-2">
                            <a href="{{ route('alumnos.show', $alumno) }}">Ver</a>
                            @can('update', $alumno)
                                <a href="{{ route('alumnos.edit', $alumno) }}">Editar</a>
                            @endcan
                            @can('delete', $alumno)
                                <form action="{{ route('alumnos.destroy', $alumno) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar alumno?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="!bg-transparent !p-0 text-sm font-normal text-red-600 hover:!bg-transparent hover:underline">Eliminar</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-slate-400">Sin alumnos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $alumnos->links() }}</div>
@endsection
