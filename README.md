## Para iniciar un chat

- Agregar un usuario como tu contacto
  - Solo se permite agregar email de usuarios que se encuentren registrado al sistema.

## Iniciar la app con Pusher:

- Crear una aplicacion en https://pusher.com/channels y obtener sus keys.
- Cambiar el broadcast driver: log a pusher. (Nota: este driver se utiliza tambien con websockets)
  - `BROADCAST_DRIVER=log // pusher`
- Panel de depuración
  - https://dashboard.pusher.com/apps/1706904/console

## Iniciar la app con Laravel WebSockets:

- Iniciar el servidor Laravel WebSocket:
  - `php artisan websockets:serve`
  - https://beyondco.de/docs/laravel-websockets/basic-usage/starting
- Panel de depuración
  - La ubicación predeterminada del panel de WebSocket es: /laravel-websockets
  - Ejemplo: `http://localhost:8000/laravel-websockets`
  - https://beyondco.de/docs/laravel-websockets/debugging/dashboard
- Nota: para Laravel WebSocket se utiliza el driver pusher y algunas configuraciones más.
  - `BROADCAST_DRIVER=log // pusher`
  - https://beyondco.de/docs/laravel-websockets/basic-usage/pusher

## Para ambas configuraciones:

- Cambiar queue connection: sync a database
  - `QUEUE_CONNECTION=sync // database`
- Si ya se agrego un monton de jobs en la cola, primero ejecutar:
  - `php artisan queue:clear`
- Iniciar los queue para el envío de los mensajes y dejarlo correr en la consola (solo para pruebas)
  - Ejecutar:
    - `php artisan queue:work`
