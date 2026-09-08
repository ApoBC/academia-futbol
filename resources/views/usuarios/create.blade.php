@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Nuevo usuario</h1>

    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf
            @include('usuarios._form', ['usuario' => null])
            <button type="submit" class="mt-5">Guardar</button>
        </form>
    </div>
@endsection
