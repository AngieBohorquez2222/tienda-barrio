<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

require_once __DIR__ . '/../includes/header.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = db()->prepare('SELECT id, nombre, imagen, precio, stock FROM productos WHERE id = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    set_flash('danger', 'Producto no encontrado.');
    redirect('/tienda-de-barrio/admin/productos.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');
    $precio = (float) ($_POST['precio'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);

    try {
        $imagenSubida = upload_product_image('imagen_file');
        if ($imagenSubida !== null) {
            $imagen = $imagenSubida;
        }
    } catch (Throwable $e) {
        set_flash('danger', $e->getMessage());
        redirect('/tienda-de-barrio/admin/editar.php?id=' . $id);
    }

    if ($imagen === '') {
        $imagen = '/tienda-de-barrio/img/productos/default.svg';
    }

    if ($nombre === '' || $precio < 0 || $stock < 0) {
        set_flash('danger', 'Datos invalidos.');
        redirect('/tienda-de-barrio/admin/editar.php?id=' . $id);
    }

    $update = db()->prepare('UPDATE productos SET nombre = ?, imagen = ?, precio = ?, stock = ? WHERE id = ?');
    $update->execute([$nombre, $imagen, $precio, $stock, $id]);

    set_flash('success', 'Producto actualizado correctamente.');
    redirect('/tienda-de-barrio/admin/productos.php');
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
            <a href="/tienda-de-barrio/admin/productos.php" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
