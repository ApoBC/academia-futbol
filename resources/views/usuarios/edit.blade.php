@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Editar usuario</h1>

    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')
            @include('usuarios._form')
            <button type="submit" class="mt-5">Actualizar</button>
        </form>
    </div>
@endsection
