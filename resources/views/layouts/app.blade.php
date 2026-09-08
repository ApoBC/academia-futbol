<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Academia Fútbol - @yield('title', 'Panel')</title>
    @yield('head')
</head>
<body>
    <nav>
        @auth
            <a href="{{ route('dashboard') }}">Dashboard</a> |
            <a href="{{ route('alumnos.index') }}">Alumnos</a> |
            <a href="{{ route('pagos.index') }}">Pagos</a> |
            @role('profesor|admin')
                <a href="{{ route('escaneo.index') }}">Escanear</a> |
            @endrole
            @role('admin')
                <a href="{{ route('reportes.dashboard') }}">Reportes</a> |
            @endrole
            <span>{{ auth()->user()->nombre }} ({{ auth()->user()->rol->value }})</span> |
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit">Salir</button>
            </form>
        @endauth
    </nav>
    <hr>

    @if (session('status'))
        <p><strong>{{ session('status') }}</strong></p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <main>
        @yield('content')
    </main>
</body>
</html>
