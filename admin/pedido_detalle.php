<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$pdo = db();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Pedido no valido.');
    redirect('/tienda-de-barrio/admin/pedidos.php');
}

$estadosPermitidos = ['pendiente', 'preparando', 'en_camino', 'entregado', 'cancelado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'] ?? '';

    if (!in_array($estado, $estadosPermitidos, true)) {
        set_flash('danger', 'Estado no permitido.');
        redirect('/tienda-de-barrio/admin/pedido_detalle.php?id=' . $id);
    }

    $stmtEstadoActual = $pdo->prepare('SELECT estado, cliente_nombre, cliente_telefono FROM pedidos WHERE id = ?');
    $stmtEstadoActual->execute([$id]);
    $estadoActual = $stmtEstadoActual->fetch();

    $update = $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
    $update->execute([$estado, $id]);

    send_whatsapp_notification($id, $estado, (string) $estadoActual['estado'], (string) $estadoActual['cliente_telefono'], (string) $estadoActual['cliente_nombre']);

    set_flash('success', 'Estado actualizado y notificación enviada por WhatsApp.');
    redirect('/tienda-de-barrio/admin/pedido_detalle.php?id=' . $id);
}

$stmtPedido = $pdo->prepare('SELECT id, codigo, cliente_nombre, cliente_telefono, cliente_direccion, total, estado, creado_en FROM pedidos WHERE id = ?');
$stmtPedido->execute([$id]);
$pedido = $stmtPedido->fetch();

if (!$pedido) {
    set_flash('danger', 'Pedido no encontrado.');
    redirect('/tienda-de-barrio/admin/pedidos.php');
}

$stmtItems = $pdo->prepare('SELECT nombre_producto, precio_unitario, cantidad, subtotal FROM pedido_items WHERE pedido_id = ? ORDER BY id');
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

$estadoClase = [
    'pendiente' => 'warning',
    'preparando' => 'info',
    'en_camino' => 'primary',
    'entregado' => 'success',
    'cancelado' => 'secondary',
];

$estadoNombre = [
    'pendiente' => 'Pendiente',
    'preparando' => 'Preparando',
    'en_camino' => 'En camino',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="table-card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 m-0">Pedido <?= htmlspecialchars($pedido['codigo']) ?></h2>
        <a href="/tienda-de-barrio/admin/pedidos.php" class="btn btn-outline-secondary btn-sm">Volver</a>
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
