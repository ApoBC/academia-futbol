<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubirColaSyncRequest;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(private readonly SyncService $syncService) {}

    /**
     * El profesor descarga esto al empezar el día (o al reconectar) y lo
     * guarda en IndexedDB para poder escanear sin conexión.
     */
    public function descargar(Request $request): JsonResponse
    {
        return response()->json([
            'generado_en' => now()->toIso8601String(),
            'alumnos' => $this->syncService->paqueteDescarga($request->user()),
        ]);
    }

    /**
     * Recibe la cola de escaneos hechos sin conexión y los procesa uno a uno
     * con la misma validación que un escaneo en vivo (Fase 4).
     */
    public function subir(SubirColaSyncRequest $request): JsonResponse
    {
        $resultados = $this->syncService->procesarCola(
            $request->input('items'),
            $request->user(),
            $request->ip(),
            $request->input('dispositivo'),
        );

        return response()->json(['ok' => true, 'resultados' => $resultados]);
    }
}
