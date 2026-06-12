# Mini Tienda de Barrio (PHP + MySQL)

Proyecto base para practicar:

- CRUD de productos.
- Catalogo en tarjetas Bootstrap.
- Carrito con sesiones.
- Validacion de stock al agregar y al finalizar compra.
- Flujo de pedidos con datos del cliente y seguimiento de estado.

## 1) Requisitos

- XAMPP (Apache + MySQL)
- PHP 8+

## 2) Configurar base de datos

1. Abrir `http://localhost/phpmyadmin`
2. Importar el archivo `bd/schema.sql`

## 3) Ejecutar proyecto

1. Copiar la carpeta en `C:/xampp/htdocs/tienda-de-barrio`
2. Iniciar Apache y MySQL en XAMPP
3. Abrir: `http://localhost/tienda-de-barrio/index.php`

## 4) Rutas principales

- Catalogo: `/tienda-de-barrio/index.php`
- Carrito: `/tienda-de-barrio/carrito.php`
- Seguimiento de pedidos: `/tienda-de-barrio/seguimiento.php`
- Registro de usuarios: `/tienda-de-barrio/registro.php`
- Login usuario: `/tienda-de-barrio/login.php`
- Login admin: `/tienda-de-barrio/admin/login.php`
- Admin productos: `/tienda-de-barrio/admin/productos.php`
- Admin pedidos: `/tienda-de-barrio/admin/pedidos.php`

## 5) Credenciales admin iniciales

- Usuario: `admin`
- Contrasena: `admin123`
- Recomendado: cambiar la contrasena en la BD para pruebas reales.

## 6) Notas didacticas

- Conexion con PDO en `config.php`.
- Funciones de apoyo en `includes/functions.php`.
- Se usan sentencias preparadas para evitar SQL Injection.
- Checkout genera un pedido real en `pedidos` y su detalle en `pedido_items`.
- El admin puede cambiar estado del pedido: pendiente, preparando, en camino, entregado o cancelado.
- **Notificaciones por WhatsApp:** Cada cambio de estado envía una notificación al cliente (tabla `notificaciones`).
  - Para integración real, configura la API de WhatsApp (Twilio, Click, etc) en la función `send_whatsapp_notification()` en `includes/functions.php`.
