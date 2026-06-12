<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

require_once __DIR__ . '/../includes/header.php';

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
        redirect('/tienda-de-barrio/admin/crear.php');
    }

    if ($imagen === '') {
        $imagen = '/tienda-de-barrio/img/productos/default.svg';
    }

    if ($nombre === '' || $precio < 0 || $stock < 0) {
        set_flash('danger', 'Datos invalidos. Revisa nombre, precio y stock.');
        redirect('/tienda-de-barrio/admin/crear.php');
    }

    $stmt = db()->prepare('INSERT INTO productos (nombre, imagen, precio, stock) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nombre, $imagen, $precio, $stock]);

    set_flash('success', 'Producto creado correctamente.');
    redirect('/tienda-de-barrio/admin/productos.php');
}
?>

<div class="form-card p-3 p-md-4 mx-auto" style="max-width: 640px;">
    <h2 class="h4 mb-3">Crear producto</h2>

    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-12">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" maxlength="120" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" min="0" name="precio" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Stock</label>
            <input type="number" min="0" name="stock" class="form-control" required>
        </div>
        <div class="col-12">
            <label class="form-label">URL de imagen</label>
            <input type="text" name="imagen" class="form-control" value="/tienda-de-barrio/img/productos/default.svg">
            <small class="text-muted">Opcional. Ejemplo: /tienda-de-barrio/img/productos/arroz.svg</small>
        </div>
        <div class="col-12">
            <label class="form-label">Subir imagen desde tu equipo</label>
            <input type="file" name="imagen_file" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            <small class="text-muted">Si subes archivo, se usa esa imagen por encima de la URL.</small>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-success" type="submit">Guardar</button>
            <a href="/tienda-de-barrio/admin/productos.php" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
