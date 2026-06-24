<?php
// Activa el modo estricto de tipos para mayor seguridad
declare(strict_types=1);

// Incluye las funciones auxiliares del proyecto
require_once __DIR__ . '/../includes/functions.php';
// Requiere autenticación de administrador, redirige al login si no está logueado
require_admin_auth();

// Incluye el header con navbar y estructura HTML básica
require_once __DIR__ . '/../includes/header.php';

// Obtiene todos los productos ordenados por ID descendente (últimos primero)
$productos = db()->query('SELECT id, nombre, imagen, precio, stock FROM productos ORDER BY id DESC')->fetchAll();
?>

<div class="table-card p-3 p-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 m-0">Administracion de productos</h2>
        <div class="d-flex gap-2">
            <a href="/tienda-barrio/admin/pedidos.php" class="btn btn-outline-primary btn-sm">Ver pedidos</a>
            <a href="/tienda-barrio/admin/crear.php" class="btn btn-success btn-sm">Nuevo producto</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?= (int) $producto['id'] ?></td>
                        <td>
                            <img src="<?= htmlspecialchars((string) $producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" style="width: 56px; height: 40px; object-fit: cover; border-radius: 0.5rem;" onerror="this.onerror=null;this.src='/tienda-barrio/img/productos/default.svg';">
                        </td>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td><?= money((float) $producto['precio']) ?></td>
                        <td><?= (int) $producto['stock'] ?></td>
                        <td class="d-flex gap-2">
                            <a href="/tienda-barrio/admin/editar.php?id=<?= (int) $producto['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <form method="post" action="/tienda-barrio/admin/eliminar.php" onsubmit="return confirm('Eliminar este producto?');">
                                <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
