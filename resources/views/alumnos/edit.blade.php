@extends('layouts.app')

@section('title', 'Editar alumno')

@section('content')
    <h1>Editar alumno</h1>
    <form method="POST" action="{{ route('alumnos.update', $alumno) }}">
        @csrf
        @method('PUT')
        @include('alumnos._form')
        <button type="submit">Actualizar</button>
    </form>
@endsection
