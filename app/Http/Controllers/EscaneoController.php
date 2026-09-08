<?php

namespace App\Http\Controllers;

use App\Enums\MotivoFalloEscaneo;
use App\Http\Requests\EscanearRequest;
use App\Http\Resources\EscaneoAdminResource;
use App\Http\Resources\EscaneoProfesorResource;
use App\Services\EscaneoService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class EscaneoController extends Controller
{
    public function __construct(private readonly EscaneoService $escaneoService) {}

    public function pantalla(): View
    {
        return view('escaneo.index');
    }

    public function escanear(EscanearRequest $request): JsonResponse
    {
        $resultado = $this->escaneoService->escanear(
            $request->contenido_qr,
            $request->user(),
            $request->ip(),
        );

        if (! $resultado->exitoso) {
            return response()->json([
                'ok' => false,
                'motivo' => $resultado->motivoFallo?->value ?? MotivoFalloEscaneo::UuidNoEncontrado->value,
            ], 422);
        }

        $resource = $request->user()->esAdmin()
            ? new EscaneoAdminResource($resultado)
            : new EscaneoProfesorResource($resultado);

        return response()->json(['ok' => true, 'data' => $resource]);
    }
}
