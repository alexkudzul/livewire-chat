/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Using Laravel Echo with Pusher
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

// Using Laravel Echo with Laravel WebSockets
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY, // Otra opción es usar la variable global window.PUSHER_APP_KEY de layouts/chat.blade.php
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: window.location.hostname,

    /**
     * Nota: Si la app pasa a producción, cambiar la siguiente configuración:
     *
     * El puerto 6001 es el puerto predeterminado para Laravel WebSockets
     * Si en el servidor ya se encuentra en uso el puerto 6001, cambiarlo por otro
     * ejemplo: 6002 y hacer la siguiente configuración:
     * wsPort:  window.APP_ENV ? 6002 : 6001
     * wssPort:  window.APP_ENV ? 6002 : 6001
     *
     * Configurar Laravel Echo "wssPort" para que también use el puerto 6001
     */
    wsPort: 6001,
    wssPort: 6001,

    /**
     * Nota: Si la app pasa a producción, cambiar la siguiente configuración:
     *
     * forceTLS: Configurar en false, si su servidor Laravel WebSocket no utiliza HTTPS
     * window.APP_ENV variable global que se encuentra en layouts/chat.blade.php
     */
    forceTLS: window.APP_ENV,

    disableStats: true,
});

/**
 * Nota:
 *
 * Iniciar el servidor Laravel WebSocket
 * Ejecutar el siguiente comando en una terminal:
 * php artisan websockets:serve
 * https://beyondco.de/docs/laravel-websockets/basic-usage/starting
 *
 * Panel de depuración
 * La ubicación predeterminada del panel de WebSocket es: /laravel-websockets
 * Ejemplo: http://localhost:8000/laravel-websockets
 * https://beyondco.de/docs/laravel-websockets/debugging/dashboard
 */
