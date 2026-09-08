@extends('layouts.app')

@section('title', 'Nuevo alumno')

@section('content')
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Nuevo alumno</h1>

    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <form method="POST" action="{{ route('alumnos.store') }}">
            @csrf
            @include('alumnos._form', ['alumno' => null])
            <button type="submit" class="mt-5">Guardar</button>
        </form>
    </div>
@endsection
