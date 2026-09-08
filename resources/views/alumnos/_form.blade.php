{{-- Parcial reutilizado por create.blade.php y edit.blade.php --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-600">Padre/Tutor</label>
        <select name="padre_id" required class="w-full">
            <option value="">-- Seleccionar --</option>
            @foreach ($padres as $padre)
                <option value="{{ $padre->id }}" @selected(old('padre_id', $alumno?->padre_id) == $padre->id)>
                    {{ $padre->nombre }} ({{ $padre->email }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Nombre completo</label>
        <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $alumno?->nombre_completo) }}" required class="w-full">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">DNI (opcional)</label>
        <input type="text" name="dni" value="{{ old('dni', $alumno?->dni) }}" class="w-full">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $alumno?->fecha_nacimiento?->format('Y-m-d')) }}" required class="w-full">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Categoría</label>
        <select name="categoria" class="w-full">
            <option value="" @selected(old('categoria') === null)>Auto (calcular por edad)</option>
            @foreach (App\Enums\CategoriaAlumno::cases() as $categoria)
                <option value="{{ $categoria->value }}" @selected(old('categoria', $alumno?->categoria?->value) === $categoria->value)>
                    {{ $categoria->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Sexo</label>
        <select name="sexo" class="w-full">
            <option value="">-- No especificado --</option>
            <option value="M" @selected(old('sexo', $alumno?->sexo) == 'M')>Masculino</option>
            <option value="F" @selected(old('sexo', $alumno?->sexo) == 'F')>Femenino</option>
        </select>
    </div>

    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-600">Alergias / enfermedades</label>
        <textarea name="alergias_enfermedades" rows="3" class="w-full">{{ old('alergias_enfermedades', $alumno?->alergias_enfermedades) }}</textarea>
    </div>

    <div class="flex items-center gap-2 sm:col-span-2">
        <input type="checkbox" name="estado_salud_alerta" value="1" id="estado_salud_alerta" @checked(old('estado_salud_alerta', $alumno?->estado_salud_alerta))>
        <label for="estado_salud_alerta" class="text-sm text-slate-600">Marcar alerta de salud visible para el profesor</label>
    </div>
</div>
