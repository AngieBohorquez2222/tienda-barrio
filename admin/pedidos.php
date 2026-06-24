<?php
// Activa el modo estricto de tipos para mayor seguridad
declare(strict_types=1);

// Incluye las funciones auxiliares del proyecto
require_once __DIR__ . '/../includes/functions.php';
// Requiere autenticación de administrador, redirige al login si no está logueado
require_admin_auth();

// Incluye el header con navbar y estructura HTML básica
require_once __DIR__ . '/../includes/header.php';

// Obtiene conexión a la base de datos
$pdo = db();
// Obtiene el filtro de estado desde GET (ej: pendiente, entregado, etc)
$estadoFiltro = trim((string) ($_GET['estado'] ?? ''));
// Obtiene el término de búsqueda desde GET (busca en código, nombre o teléfono)
$busqueda = trim((string) ($_GET['q'] ?? ''));

// Lista de estados válidos para filtrar en el formulario
$estadosPermitidos = ['pendiente', 'preparando', 'en_camino', 'entregado', 'cancelado'];
// Si el filtro no es válido, lo limpia
if ($estadoFiltro !== '' && !in_array($estadoFiltro, $estadosPermitidos, true)) {
    $estadoFiltro = '';
}

// Query base para obtener pedidos con todos sus datos
$sql = 'SELECT id, codigo, cliente_nombre, cliente_telefono, cliente_direccion, total, estado, creado_en FROM pedidos';
$where = []; // Array para acumular condiciones WHERE
$params = []; // Array para parámetros vinculados (evita SQL injection)

// Agrega filtro por estado si se especificó
if ($estadoFiltro !== '') {
    $where[] = 'estado = ?';
    $params[] = $estadoFiltro;
}

// Agrega filtro por búsqueda si se especificó (busca en código, nombre y teléfono)
if ($busqueda !== '') {
    $where[] = '(codigo LIKE ? OR cliente_nombre LIKE ? OR cliente_telefono LIKE ?)';
    $term = '%' . $busqueda . '%'; // Agrega comodines para búsqueda parcial
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

// Construye la cláusula WHERE si hay condiciones
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// Ordena por ID descendente (últimos pedidos primero)
$sql .= ' ORDER BY id DESC';

// Ejecuta la query con los parámetros preparados
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Mapeo de estados a clases CSS de Bootstrap para badges
$estadoClase = [
    'pendiente' => 'warning',
    'preparando' => 'info',
    'en_camino' => 'primary',
    'entregado' => 'success',
    'cancelado' => 'secondary',
];
?>

<div class="table-card p-3 p-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 m-0">Administracion de pedidos</h2>
        <a href="/tienda-barrio/admin/productos.php" class="btn btn-outline-secondary btn-sm">Volver a productos</a>
    </div>

    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label">Buscar cliente, telefono o codigo</label>
            <input
                type="text"
                name="q"
                class="form-control"
                value="<?= htmlspecialchars($busqueda) ?>"
                placeholder="Ej: PED-250515, Maria, 300...">
        </div>
        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($estadosPermitidos as $estado): ?>
                    <option value="<?= $estado ?>" <?= $estadoFiltro === $estado ? 'selected' : '' ?>>
                        <?= htmlspecialchars(strtoupper(str_replace('_', ' ', $estado))) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/tienda-barrio/admin/pedidos.php" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    <p class="text-muted small mb-3">Resultados: <?= count($pedidos) ?> pedido(s)</p>

    <?php if (!$pedidos): ?>
        <p class="text-muted m-0">No hay pedidos para el filtro seleccionado.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($pedido['codigo']) ?></strong><br>
                                <small class="text-muted">#<?= (int) $pedido['id'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                            <td>
                                <?= htmlspecialchars($pedido['cliente_telefono']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($pedido['cliente_direccion']) ?></small>
                            </td>
                            <td><?= money((float) $pedido['total']) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $estadoClase[$pedido['estado']] ?? 'dark' ?>">
                                    <?= strtoupper(str_replace('_', ' ', $pedido['estado'])) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars((string) $pedido['creado_en']) ?></td>
                            <td>
                                <a href="/tienda-barrio/admin/pedido_detalle.php?id=<?= (int) $pedido['id'] ?>" class="btn btn-primary btn-sm">Gestionar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
