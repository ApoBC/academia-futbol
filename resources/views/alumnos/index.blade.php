@extends('layouts.app')

@section('title', 'Alumnos')

@section('content')
    <h1>Alumnos</h1>

    @can('create', App\Models\Alumno::class)
        <p><a href="{{ route('alumnos.create') }}">+ Nuevo alumno</a></p>
    @endcan

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>Nombre</th>
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
                    <td>{{ $alumno->nombre_completo }}</td>
                    <td>{{ $alumno->categoria?->label() }}</td>
                    <td>{{ $alumno->estado->value }}</td>
                    <td>{{ $alumno->padre->nombre }}</td>
                    <td>{{ $alumno->fecha_expiracion?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('alumnos.show', $alumno) }}">Ver</a>
                        @can('update', $alumno)
                            | <a href="{{ route('alumnos.edit', $alumno) }}">Editar</a>
                        @endcan
                        @can('delete', $alumno)
                            | <form action="{{ route('alumnos.destroy', $alumno) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar alumno?')">
                                @csrf @method('DELETE')
                                <button type="submit">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Sin alumnos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $alumnos->links() }}
@endsection
