<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Alumno;
use App\Models\Pago;
use App\Models\Plan;
use App\Services\PagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PagoController extends Controller
{
    public function __construct(private readonly PagoService $pagoService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Pago::class);

        $pagos = auth()->user()->hasRole('admin')
            ? Pago::with(['alumno', 'plan', 'admin'])->latest('fecha_pago')->paginate(20)
            : Pago::with(['alumno', 'plan'])
                ->whereHas('alumno', fn ($q) => $q->where('padre_id', auth()->id()))
                ->latest('fecha_pago')->paginate(20);

        return view('pagos.index', compact('pagos'));
    }

    public function create(): View
    {
        $this->authorize('create', Pago::class);

        $alumnos = Alumno::orderBy('nombre_completo')->get();
        $planes = Plan::where('activo', true)->orderBy('nombre')->get();

        return view('pagos.create', compact('alumnos', 'planes'));
    }

    public function store(StorePagoRequest $request): RedirectResponse
    {
        $alumno = Alumno::findOrFail($request->alumno_id);
        $plan = Plan::findOrFail($request->plan_id);

        $pago = $this->pagoService->registrarPago($alumno, $plan, auth()->user(), $request->validated());

        return redirect()->route('pagos.show', $pago)->with('status', 'Pago registrado correctamente.');
    }

    public function show(Pago $pago): View
    {
        $this->authorize('view', $pago);

        return view('pagos.show', compact('pago'));
    }
}
