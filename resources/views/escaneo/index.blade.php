@extends('layouts.app')

@section('title', 'Escanear QR')

@section('content')
    <h1>Escanear carné</h1>
    <p><a href="{{ route('asistencias.hoy') }}">Ver asistencias de hoy</a></p>

    <div id="reader" style="width: 320px;"></div>

    <div id="resultado" style="margin-top: 16px; font-size: 18px;"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        const resultadoDiv = document.getElementById('resultado');
        let procesando = false;

        function mostrarResultado(html, color) {
            resultadoDiv.innerHTML = html;
            resultadoDiv.style.color = color;
        }

        async function onScanSuccess(decodedText) {
            if (procesando) return;
            procesando = true;

            try {
                const response = await fetch(@json(route('escaneo.escanear')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ contenido_qr: decodedText }),
                });
                const json = await response.json();

                if (!json.ok) {
                    mostrarResultado('❌ QR inválido: ' + json.motivo, 'red');
                } else {
                    const alumno = json.data.alumno;
                    const colores = { VERDE: 'green', AMBAR: 'orange', ROJO: 'red' };
                    let texto = `<strong>${alumno.nombre_completo}</strong> (${alumno.categoria}) — ${json.data.semaforo}`;
                    if (alumno.estado_salud_alerta) {
                        texto += `<br>⚠ Alerta de salud: ${alumno.alergias_enfermedades}`;
                    }
                    if (json.data.ya_registrado_hoy) {
                        texto += '<br><em>(ya estaba registrado hoy)</em>';
                    }
                    mostrarResultado(texto, colores[json.data.semaforo] || 'black');
                }
            } catch (e) {
                mostrarResultado('❌ Error de red al escanear.', 'red');
            }

            setTimeout(() => { procesando = false; }, 2000);
        }

        const html5QrCode = new Html5Qrcode('reader');
        html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: 250 },
            onScanSuccess
        ).catch(() => {
            mostrarResultado('No se pudo acceder a la cámara. Verifica los permisos del navegador.', 'red');
        });
    </script>
@endsection
