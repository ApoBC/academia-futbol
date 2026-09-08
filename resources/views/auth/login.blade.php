@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <h1>Iniciar sesión</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label><br>
        <label>Contraseña <input type="password" name="password" required></label><br>
        <label><input type="checkbox" name="remember"> Recuérdame</label><br>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="{{ route('register') }}">Registrarme como padre</a></p>
@endsection
