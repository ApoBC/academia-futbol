@extends('layouts.app')

@section('title', 'Registro')

@section('content')
    <h1>Registro de padre/tutor</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label>Nombre completo <input type="text" name="nombre" value="{{ old('nombre') }}" required></label><br>
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label><br>
        <label>Teléfono <input type="text" name="telefono" value="{{ old('telefono') }}"></label><br>
        <label>DNI <input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}"></label><br>
        <label>Contraseña <input type="password" name="password" required></label><br>
        <label>Confirmar contraseña <input type="password" name="password_confirmation" required></label><br>
        <button type="submit">Crear cuenta</button>
    </form>
    <p><a href="{{ route('login') }}">Ya tengo cuenta</a></p>
@endsection
