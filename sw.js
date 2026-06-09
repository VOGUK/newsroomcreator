// Newsroom Creator — Service Worker
// Strategy: network-first for all requests (app is always fresh).
// The SW is required to satisfy PWA installability criteria but intentionally
// keeps no persistent cache of sensitive content.

const CACHE_NAME = 'newsroom-creator-v1';

// Only cache the feather-icons library (static, versioned CDN asset).
const PRECACHE = [
    'https://unpkg.com/feather-icons'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE).catch(() => {}))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Never intercept api.php calls — always go to network
    if (url.pathname.endsWith('api.php')) {
        return; // pass through to browser default (network)
    }

    // For the CDN icon library, use cache-first
    if (url.origin === 'https://unpkg.com') {
        event.respondWith(
            caches.match(event.request).then(cached =>
                cached || fetch(event.request).then(resp => {
                    if (resp && resp.status === 200) {
                        const clone = resp.clone();
                        caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    }
                    return resp;
                })
            )
        );
        return;
    }

    // Everything else: network-first, fall back to cache
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
