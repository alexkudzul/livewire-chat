<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @stack('css')
    @livewireStyles

    {{-- Variables global para configurar WebSockets dependiente si la app se encuentra en local o producción --}}
    <script>
        // Key de Pusher
        // window.PUSHER_APP_KEY = '{{ config('broadcasting.connections.pusher.key') }}';

        // Si su servidor Laravel WebSocket no utiliza HTTPS, configure forceTLS en false
        if ({{ config('app.env') == 'local' }}) {
            window.APP_ENV = false;
        } else {
            window.APP_ENV = true;
        }
    </script>
</head>

<body class="font-sans antialiased">

    <div class="h-32 bg-teal-600">
    </div>

    <div class="absolute left-0 top-6 w-screen">
        <div class="container mx-auto">
            {{ $slot }}
        </div>
    </div>

    @stack('modals')

    @livewireScripts

    @stack('js')
</body>

</html>
