importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCRm4e0G2h6u1GX7ndIkq8GdOSQab3oj30",
    projectId: "smart-mall-e087a",
    storageBucket: "smart-mall-e087a.firebasestorage.app",
    messagingSenderId: "1003727523085",
    appId: "1:1003727523085:android:9a8993bd9031e65e07b444"
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage(function (payload) {
    console.log('[messaging-sw.js] Background message received:', payload);

    const notificationTitle = payload.notification?.title || 'Smart Mall';
    const notificationOptions = {
        body: payload.notification?.body || '',
        icon: '/reference/assets/images/logo-icon.png',
        badge: '/reference/assets/images/logo-icon.png',
        data: payload.data || {}
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = event.notification.data?.url || 'https://smartmall.unaux.com';
    event.waitUntil(clients.openWindow(url));
});
