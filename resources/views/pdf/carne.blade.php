<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: sans-serif; margin: 0; }

        .carnet {
            width: 242.65pt;
            height: 153.07pt;
            padding: 8pt 10pt;
            border: 1pt solid #0a5c36;
            display: table;
        }
        .fila { display: table-row; }
        .col-texto { display: table-cell; vertical-align: top; width: 65%; }
        .col-qr { display: table-cell; vertical-align: middle; width: 35%; text-align: center; }

        .marca { font-size: 7pt; font-weight: bold; color: #0a5c36; letter-spacing: 0.5pt; text-transform: uppercase; }
        .nombre { font-size: 12pt; font-weight: bold; color: #1a1a1a; margin-top: 6pt; line-height: 1.2; }
        .categoria { font-size: 8pt; color: #555; margin-top: 2pt; }
        .vence-label { font-size: 6pt; color: #888; margin-top: 10pt; text-transform: uppercase; }
        .vence-fecha { font-size: 11pt; font-weight: bold; color: #0a5c36; }
        .version { font-size: 5.5pt; color: #aaa; margin-top: 6pt; }
        .nota { font-size: 5pt; color: #999; margin-top: 3pt; line-height: 1.3; }
    </style>
</head>
<body>
    <div class="carnet">
        <div class="fila">
            <div class="col-texto">
                <div class="marca">⚽ Academia Fútbol</div>
                <div class="nombre">{{ $alumno->nombre_completo }}</div>
                <div class="categoria">{{ $alumno->categoria?->label() }}</div>

                <div class="vence-label">Válido hasta</div>
                <div class="vence-fecha">{{ $carne->fecha_expiracion->format('d/m/Y') }}</div>

                <div class="version">Carné v{{ $carne->version }}</div>
                <div class="nota">El estado final se confirma al escanear el QR en el entrenamiento.</div>
            </div>
            <div class="col-qr">
                <img src="data:image/svg+xml;base64,{{ $qrSvg }}" width="80" height="80">
            </div>
        </div>
    </div>
</body>
</html>
