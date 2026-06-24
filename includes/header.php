<?php
// Activa el modo estricto de tipos para seguridad
declare(strict_types=1);

// Incluye las funciones auxiliares
require_once __DIR__ . '/functions.php';

// Obtiene el mensaje flash de la sesión (si existe)
$flash = get_flash();
// Cuenta los productos en el carrito de sesión
$cartCount = count($_SESSION['cart'] ?? []);
?>
<!doctype html>
<html lang="es">

<head>
    <!-- Codificación UTF-8 para caracteres especiales -->
    <meta charset="utf-8">
    <!-- Viewport para diseño responsivo en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Título de la página -->
    <title>Mini Tienda de Barrio</title>
    <!-- Bootstrap CSS desde CDN local -->
    <link rel="stylesheet" href="/tienda-barrio/css/bootstrap.min.css">
    <!-- Estilos personalizados del proyecto -->
    <link rel="stylesheet" href="/tienda-barrio/css/styles.css">
</head>

<body>
    <!-- Header principal con estilo hero -->
    <header class="hero-header py-4 mb-4">
        <!-- Contenedor con flexbox para distribución -->
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <!-- Logo y nombre de la tienda -->
            <div>
                <h1 class="h3 m-0 fw-bold">Mercadito La Esquina de Ciudad Bolivar</h1>
                <p class="m-0 opacity-75">Tienda de barrio en Ciudad Bolivar, Bogota - ventas diarias con control de productos y stock</p>
            </div>
            <!-- Navegación principal -->
            <nav class="d-flex flex-wrap gap-2 align-items-center">
                <!-- Botón catálogo - página principal -->
                <a class="btn btn-light btn-sm" href="/tienda-barrio/index.php">Catalogo</a>
                <!-- Botón carrito con contador en badge -->
                <a class="btn btn-outline-light btn-sm position-relative cart-btn" href="/tienda-barrio/carrito.php" title="Ver carrito" aria-label="Ver carrito de compras">
                    <!-- Icono SVG del carrito -->
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <!-- Badge con cantidad si hay productos en carrito -->
                    <?php if ($cartCount > 0): ?>
                        <span class="badge position-absolute top-0 start-100 translate-middle cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <!-- Botón de seguimiento de pedidos -->
                <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/seguimiento.php">Seguimiento</a>
                <!-- Si es admin logueado: muestra menú admin y salir -->
                <?php if (is_admin_logged_in()): ?>
                    <a class="btn btn-warning btn-sm" href="/tienda-barrio/admin/productos.php">Admin Productos</a>
                    <a class="btn btn-light btn-sm" href="/tienda-barrio/admin/pedidos.php">Admin Pedidos</a>
                    <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/admin/logout.php">Salir (<?= htmlspecialchars(admin_name()) ?>)</a>
                <!-- Si no es admin: muestra botón de login admin -->
                <?php else: ?>
                    <a class="btn btn-warning btn-sm" href="/tienda-barrio/admin/login.php">Ingresar Admin</a>
                <?php endif; ?>
                <!-- Si es usuario logueado: muestra botón salir -->
                <?php if (is_user_logged_in()): ?>
                    <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/logout.php">Salir (<?= htmlspecialchars(user_name()) ?>)</a>
                <!-- Si no es usuario: muestra login y registro -->
                <?php else: ?>
                    <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/login.php">Ingresar</a>
                    <a class="btn btn-outline-light btn-sm" href="/tienda-barrio/registro.php">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container pb-5">
        <!-- Muestra mensaje flash si existe (danger, success, warning, etc) -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> shadow-sm"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

<?php