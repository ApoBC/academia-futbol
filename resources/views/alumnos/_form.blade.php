{{-- Parcial reutilizado por create.blade.php y edit.blade.php --}}
<label>Padre/Tutor
    <select name="padre_id" required>
        <option value="">-- Seleccionar --</option>
        @foreach ($padres as $padre)
            <option value="{{ $padre->id }}" @selected(old('padre_id', $alumno?->padre_id) == $padre->id)>
                {{ $padre->nombre }} ({{ $padre->email }})
            </option>
        @endforeach
    </select>
</label><br>

<label>Nombre completo
    <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $alumno?->nombre_completo) }}" required>
</label><br>

<label>DNI (opcional)
    <input type="text" name="dni" value="{{ old('dni', $alumno?->dni) }}">
</label><br>

<label>Fecha de nacimiento
    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $alumno?->fecha_nacimiento?->format('Y-m-d')) }}" required>
</label><br>

<label>Categoría
    <select name="categoria">
        <option value="" @selected(old('categoria') === null)>Auto (calcular por edad)</option>
        @foreach (App\Enums\CategoriaAlumno::cases() as $categoria)
            <option value="{{ $categoria->value }}" @selected(old('categoria', $alumno?->categoria?->value) === $categoria->value)>
                {{ $categoria->label() }}
            </option>
        @endforeach
    </select>
</label><br>

<label>Sexo
    <select name="sexo">
        <option value="">-- No especificado --</option>
        <option value="M" @selected(old('sexo', $alumno?->sexo) == 'M')>Masculino</option>
        <option value="F" @selected(old('sexo', $alumno?->sexo) == 'F')>Femenino</option>
    </select>
</label><br>

<label>Alergias / enfermedades
    <textarea name="alergias_enfermedades">{{ old('alergias_enfermedades', $alumno?->alergias_enfermedades) }}</textarea>
</label><br>

<label>
    <input type="checkbox" name="estado_salud_alerta" value="1" @checked(old('estado_salud_alerta', $alumno?->estado_salud_alerta))>
    Marcar alerta de salud visible para el profesor
</label><br>
