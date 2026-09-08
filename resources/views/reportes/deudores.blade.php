@extends('layouts.app')

@section('title', 'Deudores')

@section('content')
    <h1>Deudores</h1>
    <p><a href="{{ route('reportes.dashboard') }}">&larr; Dashboard</a></p>

    <form method="GET">
        <label>Categoría
            <select name="categoria" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach (App\Enums\CategoriaAlumno::cases() as $categoria)
                    <option value="{{ $categoria->value }}" @selected(request('categoria') === $categoria->value)>
                        {{ $categoria->label() }}
                    </option>
                @endforeach
            </select>
        </label>
    </form>

    <p><a href="{{ route('reportes.deudores.export', request()->query()) }}">⬇ Exportar CSV</a></p>

    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Categoría</th>
                <th>Días de mora</th>
                <th>Última asistencia</th>
                <th>Padre/Tutor</th>
                <th>Teléfono</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($deudores as $alumno)
                <tr>
                    <td>{{ $alumno->nombre_completo }}</td>
                    <td>{{ $alumno->categoria?->label() }}</td>
                    <td>{{ $alumno->dias_mora }}</td>
                    <td>{{ $alumno->ultima_asistencia ?? '—' }}</td>
                    <td>{{ $alumno->padre->nombre }}</td>
                    <td>{{ $alumno->padre->telefono ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No hay deudores 🎉</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
