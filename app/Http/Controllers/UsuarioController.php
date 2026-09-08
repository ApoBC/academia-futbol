<?php

namespace App\Http\Controllers;

use App\Enums\RolUsuario;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        // Un admin (no super) solo administra profesores; el superadmin ve a todos.
        $usuarios = auth()->user()->hasRole('superadmin')
            ? User::whereIn('rol', ['superadmin', 'admin', 'profesor'])->orderBy('nombre')->paginate(20)
            : User::where('rol', 'profesor')->orderBy('nombre')->paginate(20);

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = auth()->user()->hasRole('superadmin')
            ? RolUsuario::cases()
            : [RolUsuario::Profesor];

        return view('usuarios.create', compact('roles'));
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $rol = auth()->user()->hasRole('superadmin') ? $request->rol : RolUsuario::Profesor->value;

        User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'rol' => $rol,
        ]);

        return redirect()->route('usuarios.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        $this->authorize('update', $usuario);

        $roles = auth()->user()->hasRole('superadmin')
            ? RolUsuario::cases()
            : [RolUsuario::Profesor];

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $rol = auth()->user()->hasRole('superadmin') ? $request->rol : RolUsuario::Profesor->value;

        $usuario->update([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'rol' => $rol,
            'activo' => $request->boolean('activo'),
            ...($request->filled('password') ? ['password' => Hash::make($request->password)] : []),
        ]);

        return redirect()->route('usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $this->authorize('delete', $usuario);

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('status', 'Usuario eliminado (soft delete).');
    }
}
