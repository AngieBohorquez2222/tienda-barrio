<?php
// Activa el modo estricto de tipos para mayor seguridad
declare(strict_types=1);

// Incluye las funciones auxiliares del proyecto
require_once __DIR__ . '/../includes/functions.php';
// Requiere autenticación de administrador, redirige al login si no está logueado
require_admin_auth();

// Solo acepta peticiones POST para mayor seguridad
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/tienda-barrio/admin/productos.php');
}

// Obtiene el ID del producto a eliminar desde el formulario
$id = (int) ($_POST['id'] ?? 0);

// Validación: el ID debe ser positivo
if ($id <= 0) {
    set_flash('danger', 'ID invalido para eliminar.');
    redirect('/tienda-barrio/admin/productos.php');
}

// Elimina el producto de la base de datos (las FK en CASCADE limpian los items relacionados)
$stmt = db()->prepare('DELETE FROM productos WHERE id = ?');
$stmt->execute([$id]);

// Mensaje de confirmación y redirección
set_flash('info', 'Producto eliminado correctamente.');
redirect('/tienda-barrio/admin/productos.php');
