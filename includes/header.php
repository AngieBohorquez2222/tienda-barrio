<?php
// Activa el modo estricto de tipos para evitar conversiones implícitas de datos en PHP
declare(strict_types=1);

// Incluye el archivo de funciones auxiliares usando la ruta absoluta del directorio actual
require_once __DIR__ . '/functions.php';

// Ejecuta la función get_flash() para recuperar y borrar de la sesión cualquier mensaje temporal
$flash = get_flash();

// Cuenta los elementos dentro del array 'cart' en $_SESSION; si no existe, usa un array vacío por defecto
$cartCount = count($_SESSION['cart'] ?? []);
?>
<!doctype html>
<!-- Define el inicio del documento HTML y establece el idioma español para los lectores de pantalla -->
<html lang="es">

<head>
    <!-- Configura la codificación de caracteres UTF-8 para mostrar correctamente tildes y la letra ñ -->
    <meta charset="utf-8">
    <!-- Ajusta el ancho y la escala inicial de la página para que se adapte correctamente a pantallas móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Define el título de la pestaña que se mostrará en el navegador web -->
    <title>Mini Tienda de Barrio</title>
    <!-- Vincula la hoja de estilos de Bootstrap desde la ruta local del proyecto -->
    <link rel="stylesheet" href="/tienda-barrio/css/bootstrap.min.css">
    <!-- Vincula la hoja de estilos personalizada para aplicar reglas de diseño específicas del negocio -->
    <link rel="stylesheet" href="/tienda-barrio/css/styles.css">
</head>

<body>
    <!-- Define la cabecera principal del sitio web con un relleno vertical (py-4) y un margen inferior (mb-4) -->
    <header class="hero-header py-4 mb-4">
        <!-- Contenedor centrado de Bootstrap que usa Flexbox, permite envolver elementos y los alinea horizontalmente con espacio intermedio -->
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <!-- Bloque contenedor para el título principal y el subtítulo de la tienda -->
            <div>
                <!-- Título principal con tamaño de fuente H3, sin márgenes por defecto y con tipografía en negrita -->
                <h1 class="h3 m-0 fw-bold">Mercadito La Esquina de Ciudad Bolivar</h1>
                <!-- Párrafo descriptivo sin márgenes y con una opacidad del 75% para suavizar el color del texto -->
                <p class="m-0 opacity-75">Tienda de barrio en Ciudad Bolivar, Bogota - ventas diarias con control de productos y stock</p>
            </div>
            <!-- Bloque de navegación que organiza los enlaces en una fila flexible con separación interna mediante Flexbox -->
            <nav class="d-flex flex-wrap gap-2 align-items-center">
                <!-- Botón de estilo claro y tamaño pequeño que redirige a la página principal del catálogo -->
                <a class="btn btn-light btn-sm" href="/tienda-barrio/index.php">Catalogo</a>
                <!-- Botón con borde claro, tamaño pequeño y posición relativa que sirve como acceso directo al carrito de compras -->
                <a class="btn btn-outline-light btn-sm position-relative cart-btn" href="/tienda-barrio/carrito.php" title="Ver carrito" aria-label="Ver carrito de compras">
                    <!-- Dibuja un icono de carrito de compras mediante gráficos vectoriales SVG limpios -->
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <!-- Dibuja la rueda trasera del carrito de compras -->
                        <circle cx="9" cy="21" r="1"></circle>
                        <!-- Dibuja la rueda delantera del carrito de compras -->
                        <circle cx="20" cy="21" r="1"></circle>
                        <!-- Dibuja las líneas que forman la canasta y el manubrio del carrito de compras -->
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <!-- Bloque condicional en PHP: Evalúa si la cantidad de productos en el carrito es mayor a cero -->
                    <?php if ($cartCount > 0): ?>
                        <!-- Etiqueta flotante (badge) posicionada en la esquina superior derecha que muestra el número de productos -->
                        <span class="badge position-absolute top-0 start-100 translate-middle cart-badge"><?= $cartCount ?></span>
                    <!-- Cierre de la condición IF del carrito de compras -->
                    <?php endif; ?>
                </a>
                <!-- Botón con borde claro y tamaño pequeño para que los clientes consulten el estado de sus pedidos -->
                <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/seguimiento.php">Seguimiento</a>
                <!-- Bloque condicional en PHP: Verifica si el usuario administrador ha iniciado sesión en el sistema -->
                <?php if (is_admin_logged_in()): ?>
                    <!-- Botón de color amarillo que da acceso directo a la lista de administración de inventario y productos -->
                    <a class="btn btn-warning btn-sm" href="/tienda-barrio/admin/productos.php">Admin Productos</a>
                    <!-- Botón de color claro que permite al administrador gestionar los pedidos realizados por los clientes -->
                    <a class="btn btn-light btn-sm" href="/tienda-barrio/admin/pedidos.php">Admin Pedidos</a>
                    <!-- Botón con borde claro para cerrar sesión, sanitizando e imprimiendo dinámicamente el nombre del administrador -->
                    <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/admin/logout.php">Salir (<?= htmlspecialchars(admin_name()) ?>)</a>
                <!-- Bloque alternativo (ELSE): Se ejecuta si no hay un administrador autenticado en la sesión actual -->
                <?php else: ?>
                    <!-- Botón de color amarillo que redirige al formulario de inicio de sesión para el personal administrativo -->
                    <a class="btn btn-warning btn-sm" href="/tienda-barrio/admin/login.php">Ingresar Admin</a>
                <!-- Cierre de la condición IF/ELSE de la sesión de administrador -->
                <?php endif; ?>
                <!-- Bloque condicional en PHP: Verifica si un usuario/cliente común ha iniciado sesión en la plataforma -->
                <?php if (is_user_logged_in()): ?>
                    <!-- Botón con borde claro para cerrar sesión, sanitizando e imprimiendo el nombre del cliente en pantalla -->
                    <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/logout.php">Salir (<?= htmlspecialchars(user_name()) ?>)</a>
                <!-- Bloque alternativo (ELSE): Se ejecuta si el visitante actual es un usuario anónimo o cliente no registrado -->
                <?php else: ?>
                    <!-- Botón con borde claro que redirige al formulario de inicio de sesión para clientes -->
                    <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/login.php">Ingresar</a>
                    <!-- Botón con borde claro que desvía al visitante hacia el formulario de registro de nuevas cuentas -->
                    <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/registro.php">Registrarse</a>
                <!-- Cierre de la condición IF/ELSE de la sesión del usuario común -->
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Define el contenedor principal de la estructura semántica de la página con un espaciado inferior (pb-5) -->
    <main class="container pb-5">
        <!-- Bloque condicional en PHP: Evalúa si la variable $flash contiene datos de un mensaje informativo activo -->
        <?php if ($flash): ?>
            <!-- Caja de alerta de Bootstrap cuyo estilo de color (danger, success, etc.) y mensaje interno se inyectan de forma segura -->
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> shadow-sm"><?= htmlspecialchars($flash['message']) ?></div>
        <!-- Cierre de la condición IF del mensaje de alerta flash -->
        <?php endif; ?>
    </main>


