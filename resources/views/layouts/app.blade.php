<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academia Fútbol - @yield('title', 'Panel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('head')
</head>
<body class="antialiased">
@auth
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="flex w-60 shrink-0 flex-col bg-brand-900 text-brand-100">
            <div class="flex items-center gap-2 px-5 py-5">
                <span class="text-xl">⚽</span>
                <span class="text-lg font-semibold text-white">Academia Fútbol</span>
            </div>

            <nav class="mt-2 flex-1 space-y-1 px-3">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-brand-500 text-white' : 'hover:bg-brand-700 hover:text-white' }}">
                    🏠 Dashboard
                </a>
                <a href="{{ route('alumnos.index') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('alumnos.*') ? 'bg-brand-500 text-white' : 'hover:bg-brand-700 hover:text-white' }}">
                    🧑‍🎓 Alumnos
                </a>
                <a href="{{ route('pagos.index') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('pagos.*') ? 'bg-brand-500 text-white' : 'hover:bg-brand-700 hover:text-white' }}">
                    💳 Pagos
                </a>
                @role('profesor|admin')
                    <a href="{{ route('escaneo.index') }}"
                       class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('escaneo.*') ? 'bg-brand-500 text-white' : 'hover:bg-brand-700 hover:text-white' }}">
                        📷 Escanear
                    </a>
                    <a href="{{ route('asistencias.hoy') }}"
                       class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('asistencias.*') ? 'bg-brand-500 text-white' : 'hover:bg-brand-700 hover:text-white' }}">
                        📋 Asistencias
                    </a>
                @endrole
                @role('admin')
                    <a href="{{ route('reportes.dashboard') }}"
                       class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('reportes.*') ? 'bg-brand-500 text-white' : 'hover:bg-brand-700 hover:text-white' }}">
                        📊 Reportes
                    </a>
                @endrole
            </nav>

            <div class="border-t border-brand-700 px-3 py-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-transparent px-3 py-2 text-left text-sm font-medium text-brand-100 hover:bg-brand-700 hover:text-white">
                        ⏻ Salir
                    </button>
                </form>
            </div>
        </aside>

        {{-- Contenido --}}
        <div class="flex-1">
            {{-- Topbar --}}
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-3">
                <div class="text-sm text-slate-500">
                    <span class="text-slate-400">Panel /</span>
                    <span class="font-medium text-slate-700">@yield('title', 'Dashboard')</span>
                </div>

                <div class="flex items-center gap-4">
                    @role('admin')
                        <div class="relative">
                            <button type="button" onclick="document.getElementById('menuAgregar').classList.toggle('hidden')"
                                    class="rounded-lg bg-brand-500 px-4 py-1.5 text-sm font-medium text-white hover:bg-brand-600">
                                + Agregar
                            </button>
                            <div id="menuAgregar" class="hidden absolute right-0 z-10 mt-2 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                <a href="{{ route('alumnos.create') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">🧑‍🎓 Nuevo alumno</a>
                                <a href="{{ route('pagos.create') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">💳 Nuevo pago</a>
                            </div>
                        </div>
                    @endrole

                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white">
                            {{ mb_strtoupper(mb_substr(auth()->user()->nombre, 0, 1)) }}
                        </span>
                        <div class="text-sm leading-tight">
                            <div class="font-medium text-slate-700">{{ auth()->user()->nombre }}</div>
                            <div class="text-xs capitalize text-slate-400">{{ auth()->user()->rol->value }}</div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-brand-100 bg-brand-50 px-4 py-3 text-sm text-brand-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
@else
    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-4">
        <div class="w-full max-w-sm">
            <div class="mb-6 flex items-center justify-center gap-2">
                <span class="text-2xl">⚽</span>
                <span class="text-lg font-semibold text-slate-800">Academia Fútbol</span>
            </div>

            @if (session('status'))
                <p class="mb-4 rounded-lg border border-brand-100 bg-brand-50 px-4 py-2 text-center text-sm font-medium text-brand-700">
                    {{ session('status') }}
                </p>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @yield('content')
            </div>
        </div>
    </main>
@endauth
</body>
</html>
