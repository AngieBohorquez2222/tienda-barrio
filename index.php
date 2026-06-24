<?php
// Activa el modo estricto de tipos para seguridad
declare(strict_types=1);

// Incluye el header con navbar y apertura de HTML
require_once __DIR__ . '/includes/header.php';

// Obtiene conexión a la base de datos
$pdo = db();

// Si se envía formulario para agregar producto al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene ID del producto del formulario
    $productoId = (int) ($_POST['producto_id'] ?? 0);
    // Obtiene cantidad deseada (mínimo 1)
    $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));

    // Busca el producto en BD para validar stock
    $stmt = $pdo->prepare('SELECT id, nombre, stock FROM productos WHERE id = ?');
    $stmt->execute([$productoId]);
    $producto = $stmt->fetch();

    // Si producto no existe, muestra error
    if (!$producto) {
        set_flash('danger', 'El producto seleccionado no existe.');
        redirect('/tienda-barrio/index.php');
    }

    // Verifica que no se exceda el stock disponible
    $enCarrito = $_SESSION['cart'][$productoId] ?? 0;
    if (($enCarrito + $cantidad) > (int) $producto['stock']) {
        set_flash('warning', 'No hay stock suficiente para agregar esa cantidad.');
        redirect('/tienda-barrio/index.php');
    }

    // Agrega o suma cantidad al carrito en sesión
    $_SESSION['cart'][$productoId] = $enCarrito + $cantidad;
    set_flash('success', 'Producto agregado al carrito.');
    redirect('/tienda-barrio/index.php');
}

// Obtiene todos los productos ordenados por nombre
$productos = $pdo->query('SELECT id, nombre, imagen, precio, stock FROM productos ORDER BY nombre')->fetchAll();
?>

<!-- Sección de bienvenida -->
<section class="motivator p-3 p-md-4 mb-4">
    <!-- Título del catálogo -->
    <h2 class="h4 mb-1">Bienvenidos a Mercadito La Esquina de Ciudad Bolivar</h2>
    <!-- Descripción de la tienda -->
    <p class="m-0 text-muted">Aqui atendemos a nuestros vecinos todos los dias: controla inventario, cuida el stock y registra ventas como en una tienda real de barrio.</p>
</section>

<!-- Grid de productos -->
<div class="row g-4">
    <?php foreach ($productos as $producto): ?>
        <!-- Columna responsive para cada producto -->
        <div class="col-sm-6 col-lg-4">
            <!-- Tarjeta de producto -->
            <div class="card card-product h-100">
                <!-- Imagen del producto con fallback a default.svg -->
                <img
                    src="<?= htmlspecialchars((string) $producto['imagen']) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                    class="card-img-top product-image"
                    onerror="this.onerror=null;this.src='/tienda-barrio/img/productos/default.svg';">
                <!-- Cuerpo de la tarjeta -->
                <div class="card-body d-flex flex-column">
                    <!-- Nombre del producto -->
                    <h3 class="h5"><?= htmlspecialchars($producto['nombre']) ?></h3>
                    <!-- Precio y stock -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <!-- Precio formateado como pill -->
                        <span class="price-pill"><?= money((float) $producto['precio']) ?></span>
                        <!-- Badge de stock (verde si >0, rojo si agotado) -->
                        <span class="stock-pill badge text-bg-<?= (int) $producto['stock'] > 0 ? 'success' : 'danger' ?>">
                            Stock: <?= (int) $producto['stock'] ?>
                        </span>
                    </div>

                    <!-- Formulario para agregar al carrito -->
                    <form method="post" class="mt-auto">
                        <!-- ID del producto oculto -->
                        <input type="hidden" name="producto_id" value="<?= (int) $producto['id'] ?>">
                        <!-- Input group con cantidad y botón -->
                        <div class="input-group">
                            <!-- Input cantidad con límite de stock -->
                            <input
                                type="number"
                                name="cantidad"
                                class="form-control"
                                min="1"
                                max="<?= (int) $producto['stock'] ?>"
                                value="1"
                                <?= (int) $producto['stock'] === 0 ? 'disabled' : '' ?>
                                required>
                            <!-- Botón agregar (deshabilitado si sin stock) -->
                            <button class="btn btn-primary" type="submit" <?= (int) $producto['stock'] === 0 ? 'disabled' : '' ?>>
                                Agregar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Incluye el footer y cierre de HTML -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>