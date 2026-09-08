@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <h1 class="mb-5 text-lg font-semibold text-slate-800">Iniciar sesión</h1>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Contraseña</label>
            <input type="password" name="password" required class="w-full">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-500">
            <input type="checkbox" name="remember"> Recuérdame
        </label>
        <button type="submit" class="w-full">Entrar</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-500">
        <a href="{{ route('register') }}">Registrarme como padre</a>
    </p>
@endsection
