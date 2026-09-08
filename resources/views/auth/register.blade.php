@extends('layouts.app')

@section('title', 'Registro')

@section('content')
    <h1 class="mb-5 text-lg font-semibold text-slate-800">Registro de padre/tutor</h1>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Nombre completo</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Teléfono</label>
            <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">DNI</label>
            <input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Contraseña</label>
            <input type="password" name="password" required class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" required class="w-full">
        </div>
        <button type="submit" class="w-full">Crear cuenta</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}">Ya tengo cuenta</a>
    </p>
@endsection
