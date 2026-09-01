const CACHE_NAME = 'bems-static-v1';

const OFFLINE_FILES = [
    '/offline.html',
    '/manifest.webmanifest',
    '/images/baliangao-logo.png',
    '/images/pwa-192.png',
    '/images/pwa-512.png',
    '/images/pwa-maskable-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => cache.addAll(OFFLINE_FILES))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) =>
                Promise.all(
                    cacheNames
                        .filter((cacheName) => cacheName !== CACHE_NAME)
                        .map((cacheName) => caches.delete(cacheName)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    /*
     * Never cache authenticated employee pages or database responses.
     */
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html')),
        );

        return;
    }

    /*
     * Only serve explicitly cached public PWA assets.
     */
    if (OFFLINE_FILES.includes(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                return cachedResponse || fetch(request);
            }),
        );
    }
});