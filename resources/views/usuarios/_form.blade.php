{{-- Parcial reutilizado por create.blade.php y edit.blade.php --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Nombre completo</label>
        <input type="text" name="nombre" value="{{ old('nombre', $usuario?->nombre) }}" required class="w-full">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Email</label>
        <input type="email" name="email" value="{{ old('email', $usuario?->email) }}" required class="w-full">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Teléfono</label>
        <input type="text" name="telefono" value="{{ old('telefono', $usuario?->telefono) }}" class="w-full">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Rol</label>
        @if (count($roles) === 1)
            <input type="text" value="{{ $roles[0]->label() }}" disabled class="w-full bg-slate-50 text-slate-500">
            <input type="hidden" name="rol" value="{{ $roles[0]->value }}">
        @else
            <select name="rol" required class="w-full">
                @foreach ($roles as $rol)
                    <option value="{{ $rol->value }}" @selected(old('rol', $usuario?->rol->value) === $rol->value)>{{ $rol->label() }}</option>
                @endforeach
            </select>
        @endif
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">
            Contraseña @if($usuario) (dejar vacío para no cambiarla) @endif
        </label>
        <input type="password" name="password" {{ $usuario ? '' : 'required' }} class="w-full">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" {{ $usuario ? '' : 'required' }} class="w-full">
    </div>
    @if ($usuario)
        <div class="flex items-center gap-2 sm:col-span-2">
            <input type="checkbox" name="activo" value="1" id="activo" @checked(old('activo', $usuario->activo))>
            <label for="activo" class="text-sm text-slate-600">Usuario activo</label>
        </div>
    @endif
</div>
