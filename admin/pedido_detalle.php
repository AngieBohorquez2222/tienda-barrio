<?php
// Activa el modo estricto de tipos para mayor seguridad
declare(strict_types=1);

// Incluye las funciones auxiliares del proyecto
require_once __DIR__ . '/../includes/functions.php';
// Requiere autenticación de administrador, redirige al login si no está logueado
require_admin_auth();

// Obtiene conexión a la base de datos
$pdo = db();
// Obtiene el ID del pedido desde GET o POST (GET para ver, POST para actualizar)
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

// Validación: el ID debe ser positivo
if ($id <= 0) {
    set_flash('danger', 'Pedido no valido.');
    redirect('/tienda-barrio/admin/pedidos.php');
}

// Lista de estados válidos para el pedido
$estadosPermitidos = ['pendiente', 'preparando', 'en_camino', 'entregado', 'cancelado'];

// Maneja el formulario POST para actualizar estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene el nuevo estado seleccionado
    $estado = $_POST['estado'] ?? '';

    // Valida que el estado esté en la lista de estados permitidos
    if (!in_array($estado, $estadosPermitidos, true)) {
        set_flash('danger', 'Estado no permitido.');
        redirect('/tienda-barrio/admin/pedido_detalle.php?id=' . $id);
    }

    // Obtiene el estado actual y datos del cliente antes de actualizar
    $stmtEstadoActual = $pdo->prepare('SELECT estado, cliente_nombre, cliente_telefono FROM pedidos WHERE id = ?');
    $stmtEstadoActual->execute([$id]);
    $estadoActual = $stmtEstadoActual->fetch();

    // Actualiza el estado del pedido en la base de datos
    $update = $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
    $update->execute([$estado, $id]);

    // Envía notificación por WhatsApp al cliente sobre el cambio de estado
    send_whatsapp_notification($id, $estado, (string) $estadoActual['estado'], (string) $estadoActual['cliente_telefono'], (string) $estadoActual['cliente_nombre']);

    // Mensaje de confirmación y recarga de la página
    set_flash('success', 'Estado actualizado y notificación enviada por WhatsApp.');
    redirect('/tienda-barrio/admin/pedido_detalle.php?id=' . $id);
}

// Obtiene los datos del pedido desde la base de datos
$stmtPedido = $pdo->prepare('SELECT id, codigo, cliente_nombre, cliente_telefono, cliente_direccion, total, estado, creado_en FROM pedidos WHERE id = ?');
$stmtPedido->execute([$id]);
$pedido = $stmtPedido->fetch();

// Si el pedido no existe, muestra error y redirige
if (!$pedido) {
    set_flash('danger', 'Pedido no encontrado.');
    redirect('/tienda-barrio/admin/pedidos.php');
}

// Obtiene los items del pedido (productos incluidos)
$stmtItems = $pdo->prepare('SELECT nombre_producto, precio_unitario, cantidad, subtotal FROM pedido_items WHERE pedido_id = ? ORDER BY id');
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

// Mapeo de estados a clases CSS de Bootstrap para badges
$estadoClase = [
    'pendiente' => 'warning',
    'preparando' => 'info',
    'en_camino' => 'primary',
    'entregado' => 'success',
    'cancelado' => 'secondary',
];

// Mapeo de estados a nombres legibles en español
$estadoNombre = [
    'pendiente' => 'Pendiente',
    'preparando' => 'Preparando',
    'en_camino' => 'En camino',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];

// Incluye el header con navbar y estructura HTML básica
require_once __DIR__ . '/../includes/header.php';
?>

<div class="table-card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 m-0">Pedido <?= htmlspecialchars($pedido['codigo']) ?></h2>
        <a href="/tienda-barrio/admin/pedidos.php" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">
                <h3 class="h6 text-muted">Cliente</h3>
                <p class="m-0 fw-semibold"><?= htmlspecialchars($pedido['cliente_nombre']) ?></p>
                <p class="m-0"><?= htmlspecialchars($pedido['cliente_telefono']) ?></p>
                <p class="m-0 text-muted"><?= htmlspecialchars($pedido['cliente_direccion']) ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">
                <h3 class="h6 text-muted">Resumen</h3>
                <p class="m-0">Total: <strong><?= money((float) $pedido['total']) ?></strong></p>
                <p class="m-0">Creado: <?= htmlspecialchars((string) $pedido['creado_en']) ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">
                <h3 class="h6 text-muted">Estado actual</h3>
                <p class="mb-2">
                    <span class="badge text-bg-<?= $estadoClase[$pedido['estado']] ?? 'dark' ?>">
                        <?= htmlspecialchars($estadoNombre[$pedido['estado']] ?? $pedido['estado']) ?>
                    </span>
                </p>
                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                    <select name="estado" class="form-select form-select-sm" required>
                        <?php foreach ($estadosPermitidos as $estado): ?>
                            <option value="<?= $estado ?>" <?= $pedido['estado'] === $estado ? 'selected' : '' ?>>
                                <?= htmlspecialchars($estadoNombre[$estado]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                </form>
            </div>
        </div>
    </div>

    <h3 class="h5">Detalle del pedido</h3>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio unidad</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nombre_producto']) ?></td>
                        <td><?= money((float) $item['precio_unitario']) ?></td>
                        <td><?= (int) $item['cantidad'] ?></td>
                        <td><?= money((float) $item['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
