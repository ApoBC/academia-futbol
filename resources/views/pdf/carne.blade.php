<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; }
        .carnet { border: 2px solid #333; padding: 16px; width: 320px; }
        .nombre { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .categoria { color: #555; margin-bottom: 12px; }
        .vence { font-size: 20px; font-weight: bold; margin-top: 12px; }
        .nota { font-size: 10px; color: #777; margin-top: 12px; }
        .qr { text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="carnet">
        <div class="nombre">{{ $alumno->nombre_completo }}</div>
        <div class="categoria">{{ $alumno->categoria?->label() }}</div>

        <div class="qr">
            <img src="data:image/svg+xml;base64,{{ $qrSvg }}" width="180" height="180">
        </div>

        <div class="vence">Válido hasta: {{ $carne->fecha_expiracion->format('d/m/Y') }}</div>

        <div class="nota">
            El estado final del alumno (al día / suspendido) se confirma al escanear el QR
            en el momento del entrenamiento. Carné v{{ $carne->version }}.
        </div>
    </div>
</body>
</html>
