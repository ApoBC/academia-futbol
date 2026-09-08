<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\User;
use App\Services\CategoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AlumnoController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Alumno::class);

        $alumnos = auth()->user()->hasRole('admin')
            ? Alumno::with('padre')->latest()->paginate(20)
            : Alumno::with('padre')->where('padre_id', auth()->id())->latest()->paginate(20);

        return view('alumnos.index', compact('alumnos'));
    }

    public function create(): View
    {
        $this->authorize('create', Alumno::class);

        $padres = User::where('rol', 'padre')->orderBy('nombre')->get();

        return view('alumnos.create', compact('padres'));
    }

    public function store(StoreAlumnoRequest $request): RedirectResponse
    {
        Alumno::create($this->sinCategoriaVacia($request->validated()));

        return redirect()->route('alumnos.index')->with('status', 'Alumno registrado correctamente.');
    }

    public function show(Alumno $alumno): View
    {
        $this->authorize('view', $alumno);

        return view('alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno): View
    {
        $this->authorize('update', $alumno);

        $padres = User::where('rol', 'padre')->orderBy('nombre')->get();

        return view('alumnos.edit', compact('alumno', 'padres'));
    }

    public function update(UpdateAlumnoRequest $request, Alumno $alumno): RedirectResponse
    {
        $datos = $this->sinCategoriaVacia($request->validated());

        if (! array_key_exists('categoria', $datos)) {
            $datos['categoria'] = app(CategoriaService::class)->calcular($datos['fecha_nacimiento'])->value;
        }

        $alumno->update($datos);

        return redirect()->route('alumnos.index')->with('status', 'Alumno actualizado correctamente.');
    }

    public function destroy(Alumno $alumno): RedirectResponse
    {
        $this->authorize('delete', $alumno);

        $alumno->delete();

        return redirect()->route('alumnos.index')->with('status', 'Alumno eliminado (soft delete).');
    }

    /**
     * "Auto" en el select de categoría llega como cadena vacía: la quitamos
     * para que el modelo (creación) o este controller (edición) la calculen.
     */
    private function sinCategoriaVacia(array $datos): array
    {
        if (($datos['categoria'] ?? null) === '' || ($datos['categoria'] ?? null) === null) {
            unset($datos['categoria']);
        }

        return $datos;
    }
}
