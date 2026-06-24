<?php
// Activa el modo estricto de tipos para mayor seguridad
declare(strict_types=1);

// Incluye las funciones auxiliares del proyecto
require_once __DIR__ . '/../includes/functions.php';
// Requiere autenticación de administrador, redirige al login si no está logueado
require_admin_auth();

// Incluye el header con navbar y estructura HTML básica
require_once __DIR__ . '/../includes/header.php';

// Obtiene el ID del producto desde GET o POST (GET para cargar, POST para guardar)
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
// Busca el producto en la base de datos
$stmt = db()->prepare('SELECT id, nombre, imagen, precio, stock FROM productos WHERE id = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

// Si el producto no existe, muestra error y redirige
if (!$producto) {
    set_flash('danger', 'Producto no encontrado.');
    redirect('/tienda-barrio/admin/productos.php');
}

// Procesa el formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene nombre del producto desde el formulario
    $nombre = trim($_POST['nombre'] ?? '');
    // Obtiene URL de imagen desde el formulario
    $imagen = trim($_POST['imagen'] ?? '');
    // Obtiene precio del producto (decimal) desde el formulario
    $precio = (float) ($_POST['precio'] ?? 0);
    // Obtiene stock (cantidad disponible) desde el formulario
    $stock = (int) ($_POST['stock'] ?? 0);

    // Intenta subir una nueva imagen si el usuario la adjuntó
    try {
        $imagenSubida = upload_product_image('imagen_file');
        // Si se subió archivo, reemplaza la URL de imagen con la nueva
        if ($imagenSubida !== null) {
            $imagen = $imagenSubida;
        }
    } catch (Throwable $e) {
        // Muestra error si la imagen no pudo cargarse
        set_flash('danger', $e->getMessage());
        redirect('/tienda-barrio/admin/editar.php?id=' . $id);
    }

    // Si no hay imagen, usa la imagen por defecto
    if ($imagen === '') {
        $imagen = '/tienda-barrio/img/productos/default.svg';
    }

    // Valida que los datos sean válidos antes de actualizar
    if ($nombre === '' || $precio < 0 || $stock < 0) {
        set_flash('danger', 'Datos invalidos.');
        redirect('/tienda-barrio/admin/editar.php?id=' . $id);
    }

    // Actualiza el producto en la base de datos
    $update = db()->prepare('UPDATE productos SET nombre = ?, imagen = ?, precio = ?, stock = ? WHERE id = ?');
    $update->execute([$nombre, $imagen, $precio, $stock, $id]);

    // Mensaje de éxito y redirección a la lista de productos
    set_flash('success', 'Producto actualizado correctamente.');
    redirect('/tienda-barrio/admin/productos.php');
}
?>

<div class="form-card p-3 p-md-4 mx-auto" style="max-width: 640px;">
    <h2 class="h4 mb-3">Editar producto #<?= (int) $producto['id'] ?></h2>

    <form method="post" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">
        <div class="col-12">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" maxlength="120" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= (float) $producto['precio'] ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Stock</label>
            <input type="number" min="0" name="stock" class="form-control" value="<?= (int) $producto['stock'] ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label">URL de imagen</label>
            <input type="text" name="imagen" class="form-control" value="<?= htmlspecialchars((string) $producto['imagen']) ?>">
            <small class="text-muted">Opcional si vas a subir archivo.</small>
        </div>
        <div class="col-12">
            <label class="form-label">Subir nueva imagen</label>
            <input type="file" name="imagen_file" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            <small class="text-muted">Si subes archivo, reemplaza la URL actual.</small>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-warning" type="submit">Actualizar</button>
            <a href="/tienda-barrio/admin/productos.php" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
