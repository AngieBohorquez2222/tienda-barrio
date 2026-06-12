<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/tienda-de-barrio/admin/productos.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID invalido para eliminar.');
    redirect('/tienda-de-barrio/admin/productos.php');
}

$stmt = db()->prepare('DELETE FROM productos WHERE id = ?');
$stmt->execute([$id]);

set_flash('info', 'Producto eliminado correctamente.');
redirect('/tienda-de-barrio/admin/productos.php');
