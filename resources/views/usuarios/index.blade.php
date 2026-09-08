@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Usuarios</h1>
        <a href="{{ route('usuarios.create') }}" class="rounded-lg bg-brand-500 px-4 py-1.5 text-sm font-medium text-white hover:bg-brand-600">+ Nuevo usuario</a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->nombre }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->rol->label() }}</td>
                        <td>
                            @if ($usuario->activo)
                                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">Activo</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Inactivo</span>
                            @endif
                        </td>
                        <td class="space-x-2">
                            @can('update', $usuario)
                                <a href="{{ route('usuarios.edit', $usuario) }}">Editar</a>
                            @endcan
                            @can('delete', $usuario)
                                <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="!bg-transparent !p-0 text-sm font-normal text-red-600 hover:!bg-transparent hover:underline">Eliminar</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-400">Sin usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $usuarios->links() }}</div>
@endsection
