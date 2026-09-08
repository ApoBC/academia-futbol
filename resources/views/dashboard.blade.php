@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Bienvenido, {{ auth()->user()->nombre }}</h1>
    <p>Rol: {{ auth()->user()->rol->value }}</p>
    <p><a href="{{ route('alumnos.index') }}">Ver alumnos</a></p>
@endsection
