const CACHE = 'smartmall-v1';
const ASSETS = [
    '.',
    'offline.html',
    'assets/logo-icon.png'
];

self.addEventListener('install', (e) => {
    self.skipWaiting();
    e.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(ASSETS))
    );
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        )
    );
});

self.addEventListener('fetch', (e) => {
    if (e.request.method !== 'GET') return;

    e.respondWith(
        fetch(e.request)
            .then((res) => {
                const clone = res.clone();
                if (res.ok && res.type === 'basic') {
                    caches.open(CACHE).then((cache) => cache.put(e.request, clone));
                }
                return res;
            })
            .catch(() =>
                caches.match(e.request).then((cached) => {
                    if (cached) return cached;
                    if (e.request.headers.get('Accept')?.includes('text/html')) {
                        return caches.match('/offline.html');
                    }
                    return new Response('Offline', { status: 503 });
                })
            )
    );
});
