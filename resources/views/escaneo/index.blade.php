@extends('layouts.app')

@section('title', 'Escanear QR')

@section('head')
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0a5c36">
@endsection

@section('content')
    <h1>Escanear carné</h1>
    <p><a href="{{ route('asistencias.hoy') }}">Ver asistencias de hoy</a></p>

    <div id="estadoSync" style="margin-bottom: 12px; font-size: 14px; color: #555;">Cargando estado offline…</div>

    <div id="reader" style="width: 320px;"></div>

    <div id="resultado" style="margin-top: 16px; font-size: 18px;"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dexie/3.2.4/dexie.min.js"></script>
    <script>
        const RUTA_DESCARGAR = @json(route('sync.descargar'));
        const RUTA_SUBIR = @json(route('sync.subir'));
        const RUTA_ESCANEAR = @json(route('escaneo.escanear'));
        const CSRF_TOKEN = @json(csrf_token());

        // --- IndexedDB (Dexie): cache de alumnos + cola de asistencias pendientes ---
        const db = new Dexie('academia-futbol-offline');
        db.version(1).stores({
            alumnosCache: 'contenido_qr',
            colaPendiente: '++id, contenido_qr',
        });

        function dispositivoId() {
            let id = localStorage.getItem('academia_dispositivo_id');
            if (!id) {
                id = 'dispositivo-' + Math.random().toString(36).slice(2, 10);
                localStorage.setItem('academia_dispositivo_id', id);
            }
            return id;
        }

        const estadoDiv = document.getElementById('estadoSync');
        const resultadoDiv = document.getElementById('resultado');

        async function actualizarIndicador() {
            const pendientes = await db.colaPendiente.count();
            const cacheCount = await db.alumnosCache.count();
            const ultimaSync = localStorage.getItem('academia_ultima_sync');
            const texto = ultimaSync
                ? 'Última sync: ' + new Date(ultimaSync).toLocaleString()
                : 'Aún no se ha sincronizado';
            estadoDiv.textContent = `${texto} · ${cacheCount} alumnos en caché · ${pendientes} asistencias pendientes de sincronizar` +
                (navigator.onLine ? '' : ' · SIN CONEXIÓN');
        }

        async function descargarCache() {
            if (!navigator.onLine) return;
            try {
                const res = await fetch(RUTA_DESCARGAR, { headers: { Accept: 'application/json' } });
                if (!res.ok) return;
                const json = await res.json();
                await db.alumnosCache.clear();
                await db.alumnosCache.bulkPut(json.alumnos);
                localStorage.setItem('academia_ultima_sync', json.generado_en);
            } catch (e) {
                // Sin conexión real o error de red: seguimos con lo que ya había en caché.
            }
            actualizarIndicador();
        }

        async function sincronizarCola() {
            if (!navigator.onLine) return;
            const pendientes = await db.colaPendiente.toArray();
            if (pendientes.length === 0) return;

            try {
                const res = await fetch(RUTA_SUBIR, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        items: pendientes.map(({ contenido_qr, fecha, hora }) => ({ contenido_qr, fecha, hora })),
                        dispositivo: dispositivoId(),
                    }),
                });

                if (res.ok) {
                    await db.colaPendiente.clear();
                    localStorage.setItem('academia_ultima_sync', new Date().toISOString());
                }
            } catch (e) {
                // Se reintenta en el próximo evento 'online' o próxima carga.
            }
            actualizarIndicador();
        }

        function colores(semaforo) {
            return { VERDE: 'green', AMBAR: 'orange', ROJO: 'red' }[semaforo] || 'black';
        }

        function pintarResultado(alumno, semaforo, extra) {
            let texto = `<strong>${alumno.nombre_completo}</strong> (${alumno.categoria}) — ${semaforo}`;
            if (alumno.estado_salud_alerta) {
                texto += `<br>⚠ Alerta de salud: ${alumno.alergias_enfermedades}`;
            }
            if (extra) {
                texto += `<br><em>${extra}</em>`;
            }
            resultadoDiv.innerHTML = texto;
            resultadoDiv.style.color = colores(semaforo);
        }

        async function escanearOnline(contenidoQr) {
            const response = await fetch(RUTA_ESCANEAR, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ contenido_qr: contenidoQr }),
            });
            const json = await response.json();

            if (!json.ok) {
                resultadoDiv.innerHTML = '❌ QR inválido: ' + json.motivo;
                resultadoDiv.style.color = 'red';
                return;
            }

            pintarResultado(json.data.alumno, json.data.semaforo, json.data.ya_registrado_hoy ? '(ya estaba registrado hoy)' : null);
        }

        async function escanearOffline(contenidoQr) {
            const alumnoCache = await db.alumnosCache.get(contenidoQr);

            if (!alumnoCache) {
                resultadoDiv.innerHTML = '❌ QR no reconocido en el caché local. Conéctate para sincronizar.';
                resultadoDiv.style.color = 'red';
                return;
            }

            const yaEnCola = await db.colaPendiente.where('contenido_qr').equals(contenidoQr).count();
            const ahora = new Date();

            if (yaEnCola === 0) {
                await db.colaPendiente.add({
                    contenido_qr: contenidoQr,
                    fecha: ahora.toISOString().slice(0, 10),
                    hora: ahora.toTimeString().slice(0, 8),
                });
            }

            pintarResultado(alumnoCache, alumnoCache.semaforo, 'Guardado sin conexión, se sincronizará automáticamente.');
            actualizarIndicador();
        }

        let procesando = false;

        async function onScanSuccess(decodedText) {
            if (procesando) return;
            procesando = true;

            try {
                if (navigator.onLine) {
                    await escanearOnline(decodedText);
                } else {
                    await escanearOffline(decodedText);
                }
            } catch (e) {
                await escanearOffline(decodedText);
            }

            setTimeout(() => { procesando = false; }, 2000);
        }

        // --- Arranque ---
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }

        window.addEventListener('online', () => { sincronizarCola().then(descargarCache); });

        descargarCache().then(() => sincronizarCola());
        actualizarIndicador();

        const html5QrCode = new Html5Qrcode('reader');
        html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: 250 },
            onScanSuccess
        ).catch(() => {
            resultadoDiv.innerHTML = 'No se pudo acceder a la cámara. Verifica los permisos del navegador.';
            resultadoDiv.style.color = 'red';
        });
    </script>
@endsection
