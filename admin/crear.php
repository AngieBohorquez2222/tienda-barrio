<?php
// Activa el modo estricto de tipos para mayor seguridad en comparaciones
declare(strict_types=1);

// Incluye las funciones auxiliares del proyecto
require_once __DIR__ . '/../includes/functions.php';
// Requiere autenticación de administrador, redirige al login si no está logueado
require_admin_auth();

// Incluye el header con navbar y estructura HTML básica
require_once __DIR__ . '/../includes/header.php';

// Manejo del formulario POST para crear nuevo producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene nombre del producto desde el formulario
    $nombre = trim($_POST['nombre'] ?? '');
    // Obtiene URL de imagen opcional desde el formulario
    $imagen = trim($_POST['imagen'] ?? '');
    // Obtiene precio del producto (decimal) desde el formulario
    $precio = (float) ($_POST['precio'] ?? 0);
    // Obtiene stock (cantidad disponible) desde el formulario
    $stock = (int) ($_POST['stock'] ?? 0);

    // Intenta subir una imagen nueva si el usuario la adjuntó
    try {
        // upload_product_image maneja la carga del archivo y retorna la URL pública
        $imagenSubida = upload_product_image('imagen_file');
        // Si se subió archivo, reemplaza la URL de imagen con la nueva
        if ($imagenSubida !== null) {
            $imagen = $imagenSubida;
        }
    } catch (Throwable $e) {
        // Muestra error si la imagen no pudo cargarse
        set_flash('danger', $e->getMessage());
        redirect('/tienda-barrio/admin/crear.php');
    }

    // Si no hay imagen, usa la imagen por defecto
    if ($imagen === '') {
        $imagen = '/tienda-barrio/img/productos/default.svg';
    }

    // Valida que los datos sean válidos antes de insertar
    if ($nombre === '' || $precio < 0 || $stock < 0) {
        set_flash('danger', 'Datos invalidos. Revisa nombre, precio y stock.');
        redirect('/tienda-barrio/admin/crear.php');
    }

    // Inserta el nuevo producto en la base de datos
    $stmt = db()->prepare('INSERT INTO productos (nombre, imagen, precio, stock) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nombre, $imagen, $precio, $stock]);

    // Mensaje de éxito y redirección a la lista de productos
    set_flash('success', 'Producto creado correctamente.');
    redirect('/tienda-barrio/admin/productos.php');
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
            <input type="text" name="imagen" class="form-control" value="/tienda-barrio/img/productos/default.svg">
            <small class="text-muted">Opcional. Ejemplo: /tienda-barrio/img/productos/arroz.svg</small>
        </div>
        <div class="col-12">
            <label class="form-label">Subir imagen desde tu equipo</label>
            <input type="file" name="imagen_file" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            <small class="text-muted">Si subes archivo, se usa esa imagen por encima de la URL.</small>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-success" type="submit">Guardar</button>
            <a href="/tienda-barrio/admin/productos.php" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
