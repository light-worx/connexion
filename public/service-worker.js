const CACHE_NAME = 'connexion-v0.1';
const VERSION_INFO = {
    version: 'v0.1',
    notes: 'Initial app.'
};
const OFFLINE_URL = '/offline';

const APP_SHELL = [
    '/',
    OFFLINE_URL,
    '/manifest.json',
    '/images/logo_small.png',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
];

self.addEventListener('message', event => {
    // This allows the Blade file to "pull" the constants above
    if (event.data?.action === 'getVersionInfo') {
        event.ports[0].postMessage(VERSION_INFO);
    }

    if (event.data?.action === 'skipWaiting') {
        self.skipWaiting();
    }
});

self.addEventListener('install', event => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('[SW] Caching app shell');
            return cache.addAll(APP_SHELL);
        })
    );
});

self.addEventListener('activate', event => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys().then(cacheNames =>
            Promise.all(
                cacheNames.map(name => {
                    if (name !== CACHE_NAME) {
                        console.log('[SW] Removing old cache:', name);
                        return caches.delete(name);
                    }
                })
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('message', event => {
    if (event.data?.action === 'skipWaiting') {
        console.log('[SW] Skip waiting requested');
        self.skipWaiting();
    }
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (!url.protocol.startsWith('http')) return;

    if (url.pathname.startsWith('/livewire')) {
        return; // let browser handle it normally
    }

    // ===== Navigation requests =====
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    if (
                        !response ||
                        response.status !== 200 ||
                        response.type !== 'basic'
                    ) {
                        return response;
                    }

                    const copy = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, copy);
                    });

                    return response;
                })
                .catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // ===== Static assets =====
    event.respondWith(
        caches.match(event.request).then(cached => {
            const fetchPromise = fetch(event.request)
                .then(networkResponse => {
                    if (
                        !networkResponse ||
                        networkResponse.status !== 200 ||
                        (networkResponse.type !== 'basic' &&
                         networkResponse.type !== 'cors')
                    ) {
                        return networkResponse;
                    }

                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, copy);
                    });

                    return networkResponse;
                })
                .catch(() => cached);

            return cached || fetchPromise;
        })
    );
});

/* =========================
   PUSH NOTIFICATIONS
========================= */
self.addEventListener('push', event => {
    console.log('[SW] Push received');

    let data = {
        title: 'Corabrand',
        body: 'You have a new notification',
        icon: '/images/logo_small.png',
        badge: '/images/icons/icon-192.png',
        url: '/'
    };

    if (event.data) {
        try {
            const json = event.data.json();
            data = { ...data, ...json };
        } catch {
            data.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            vibrate: [200, 100, 200],
            data: { url: data.url }
        })
    );
});

/* =========================
   NOTIFICATION CLICK
========================= */
self.addEventListener('notificationclick', event => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientsArr => {
                for (const client of clientsArr) {
                    if (client.url === targetUrl && 'focus' in client) {
                        return client.focus();
                    }
                }
                return clients.openWindow(targetUrl);
            })
    );
});
