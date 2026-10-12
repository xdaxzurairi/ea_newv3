/**
 * e-Aduan v3 – Service Worker
 * Cache-first for static assets; halaman HTML network-only (fallback offline.html).
 * HTML TIDAK di-cache: halaman aduan mengandungi data peribadi pelapor dan
 * cache kekal pada peranti kongsi selepas log keluar.
 * Bump CACHE_VERSION → cache lama (termasuk HTML yang pernah di-cache) dipadam semasa activate.
 */
const CACHE_VERSION = 'eaduan-v3-cache-v3';
const OFFLINE_URL = '/ea_newv3/offline.html';

const PRECACHE_ASSETS = [
    '/ea_newv3/offline.html',
    '/ea_newv3/assets/css/style.css',
    '/ea_newv3/assets/js/app.js',
    '/ea_newv3/assets/js/pwa-register.js',
    '/ea_newv3/assets/js/pwa-install.js',
    '/ea_newv3/assets/icons/icon-192x192.png',
    '/ea_newv3/assets/icons/icon-512x512.png'
];

const CACHE_CDN = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then((cache) => {
            const allUrls = [...PRECACHE_ASSETS, ...CACHE_CDN];
            return Promise.allSettled(
                allUrls.map((url) => cache.add(url).catch(() => {}))
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_VERSION)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET') return;

    if (url.pathname.startsWith('/ea_newv3/ajax/')) return;

    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
    } else if (isNavigationRequest(request)) {
        event.respondWith(networkFirstWithOffline(request));
    } else {
        event.respondWith(staleWhileRevalidate(request));
    }
});

function isStaticAsset(url) {
    return /\.(css|js|png|jpg|jpeg|gif|svg|woff2?|ttf|eot|ico)$/i.test(url.pathname)
        || url.hostname === 'cdn.jsdelivr.net'
        || url.hostname === 'fonts.googleapis.com'
        || url.hostname === 'fonts.gstatic.com';
}

function isNavigationRequest(request) {
    return request.mode === 'navigate'
        || (request.headers.get('accept') || '').includes('text/html');
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('', { status: 408, statusText: 'Offline' });
    }
}

async function networkFirstWithOffline(request) {
    try {
        return await fetch(request);
    } catch {
        return caches.match(OFFLINE_URL);
    }
}

async function staleWhileRevalidate(request) {
    const cached = await caches.match(request);
    const fetchPromise = fetch(request)
        .then((response) => {
            if (response.ok) {
                const toCache = response.clone();
                caches.open(CACHE_VERSION).then((cache) => {
                    cache.put(request, toCache);
                });
            }
            return response;
        })
        .catch(() => cached);

    return cached || fetchPromise;
}
