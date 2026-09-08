// Service Worker de la PWA del profesor.
// Cachea el "shell" de la app de escaneo para que la página cargue sin
// conexión. La lógica de escaneo offline en sí (matching de QR, cola de
// asistencias pendientes) vive en IndexedDB (Dexie), no aquí — este SW
// solo resuelve "¿puedo abrir /escaneo sin internet?".

const CACHE_NAME = 'academia-futbol-v1';

const RECURSOS_APP_SHELL = [
    '/escaneo',
    '/manifest.json',
    'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/dexie/3.2.4/dexie.min.js',
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.all(
                RECURSOS_APP_SHELL.map((url) =>
                    fetch(url, { mode: 'no-cors' }).then((res) => cache.put(url, res)).catch(() => null)
                )
            );
        })
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((nombres) =>
            Promise.all(nombres.filter((n) => n !== CACHE_NAME).map((n) => caches.delete(n)))
        )
    );
    self.clients.claim();
});

// Network-first para todo, con fallback a caché cuando no hay conexión.
// Así el profesor siempre ve datos frescos si hay internet, y la última
// versión guardada si no la hay.
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return; // Los POST (escanear, sync/subir) nunca se sirven desde caché.
    }

    event.respondWith(
        fetch(event.request)
            .then((respuesta) => {
                const copia = respuesta.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copia));
                return respuesta;
            })
            .catch(() => caches.match(event.request))
    );
});
