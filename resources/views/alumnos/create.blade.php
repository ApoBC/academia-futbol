@extends('layouts.app')

@section('title', 'Nuevo alumno')

@section('content')
    <h1>Nuevo alumno</h1>
    <form method="POST" action="{{ route('alumnos.store') }}">
        @csrf
        @include('alumnos._form', ['alumno' => null])
        <button type="submit">Guardar</button>
    </form>
@endsection
