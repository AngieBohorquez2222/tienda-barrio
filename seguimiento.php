<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

// ============================================================
// INICIALIZACIÓN DE VARIABLES
// ============================================================

$pdo = db(); // Conexión a la base de datos
$pedido = null;             // Detalle del pedido encontrado (único)
$items = [];                // Productos que contiene ese pedido
$pedidosPorTelefono = [];   // Lista de pedidos al buscar solo por teléfono
$codigoBuscado = '';        // Código escrito en el formulario
$telefonoBuscado = '';      // Teléfono escrito en el formulario

// ============================================================
// ACCESO DIRECTO POR URL: ?id=X&telefono=Y
// Permite llegar al detalle desde la tabla de pedidos sin
// necesidad de escribir el código manualmente.
// ============================================================

if (isset($_GET['id'], $_GET['telefono'])) {
    $pedidoId = (int) $_GET['id'];
    $telefonoBuscado = trim((string) $_GET['telefono']);

    // Solo procesa si el id es positivo y el teléfono no está vacío
    if ($pedidoId > 0 && $telefonoBuscado !== '') {
        // Busca el pedido que coincida con id Y teléfono a la vez
        // (el teléfono actúa como verificación de identidad básica)
        $stmt = $pdo->prepare(
            'SELECT id, codigo, cliente_nombre, cliente_telefono, cliente_direccion, total, estado, creado_en
             FROM pedidos
             WHERE id = ? AND cliente_telefono = ?
             LIMIT 1'
        );
        $stmt->execute([$pedidoId, $telefonoBuscado]);
        $pedido = $stmt->fetch();

        if ($pedido) {
            // Si encontró el pedido, también carga sus ítems
            $stmtItems = $pdo->prepare(
                'SELECT nombre_producto, precio_unitario, cantidad, subtotal FROM pedido_items WHERE pedido_id = ? ORDER BY id'
            );
            $stmtItems->execute([$pedidoId]);
            $items = $stmtItems->fetchAll();
            $codigoBuscado = (string) $pedido['codigo'];
        }
    }
}

// ============================================================
// MANEJO DE FORMULARIOS POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'buscar_codigo');

    // ------------------------------------------------------------
    // ACCIÓN: buscar SOLO por teléfono (sin código)
    // Devuelve un listado de hasta 12 pedidos del número ingresado
    // ------------------------------------------------------------
    if ($action === 'buscar_telefono') {
        $telefonoBuscado = trim((string) ($_POST['telefono'] ?? ''));

        if ($telefonoBuscado === '') {
            set_flash('warning', 'Ingresa tu teléfono para ver tus pedidos.');
            redirect('/tienda-barrio/seguimiento.php');
        }

        // Trae los últimos 12 pedidos asociados al teléfono
        $stmtListado = $pdo->prepare(
            'SELECT id, codigo, total, estado, creado_en FROM pedidos WHERE cliente_telefono = ? ORDER BY id DESC LIMIT 12'
        );
        $stmtListado->execute([$telefonoBuscado]);
        $pedidosPorTelefono = $stmtListado->fetchAll();

        if (!$pedidosPorTelefono) {
            set_flash('warning', 'No encontramos pedidos asociados a ese teléfono.');
        }
    } else {
        // ------------------------------------------------------------
        // ACCIÓN: buscar por CÓDIGO + teléfono (formulario principal)
        // ------------------------------------------------------------
        $codigoBuscado = trim((string) ($_POST['codigo'] ?? ''));
        $telefonoBuscado = trim((string) ($_POST['telefono'] ?? ''));

        if ($codigoBuscado === '' || $telefonoBuscado === '') {
            set_flash('warning', 'Ingresa el código de pedido y teléfono para buscar.');
            redirect('/tienda-barrio/seguimiento.php');
        }

        // Busca el pedido que coincida con código Y teléfono a la vez
        $stmt = $pdo->prepare(
            'SELECT id, codigo, cliente_nombre, cliente_telefono, cliente_direccion, total, estado, creado_en
             FROM pedidos
             WHERE codigo = ? AND cliente_telefono = ?
             LIMIT 1'
        );
        $stmt->execute([$codigoBuscado, $telefonoBuscado]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            set_flash('danger', 'Pedido no encontrado. Verifica el codigo y telefono.');
            redirect('/tienda-barrio/seguimiento.php');
        }

        // Carga los ítems del pedido encontrado
        $stmtItems = $pdo->prepare(
            'SELECT nombre_producto, precio_unitario, cantidad, subtotal FROM pedido_items WHERE pedido_id = ? ORDER BY id'
        );
        $stmtItems->execute([(int) $pedido['id']]);
        $items = $stmtItems->fetchAll();
    }
}

// ============================================================
// MAPAS DE ESTADO → CLASE CSS / ETIQUETA / DESCRIPCIÓN
// Se usan en badges Bootstrap y textos descriptivos de la vista
// ============================================================

$estadoClase = [
    'pendiente' => 'warning',
    'preparando' => 'info',
    'en_camino' => 'primary',
    'entregado' => 'success',
    'cancelado' => 'secondary',
];

$estadoNombre = [
    'pendiente' => 'Pendiente',
    'preparando' => 'Preparando tu pedido',
    'en_camino' => 'En camino a tu domicilio',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];

$estadoDescripcion = [
    'pendiente' => 'Tu pedido ha sido registrado y está en espera de ser procesado.',
    'preparando' => 'Estamos preparando tus productos en la tienda.',
    'en_camino' => 'Tu pedido está en camino. Llegarará pronto.',
    'entregado' => '¡Tu pedido ha sido entregado con éxito!',
    'cancelado' => 'Este pedido fue cancelado.',
];

// ============================================================
// GENERACIÓN DE LINKS DE WHATSAPP
// Genérico: para consultar a la tienda sin pedido concreto.
// Específico: incluye nombre y código del pedido del cliente.
// ============================================================

$mensajeWhatsappGeneral = 'Hola, soy cliente de ' . STORE_NAME . '. Quiero consultar el estado de mi pedido. Mi telefono es: ' . $telefonoBuscado;
$linkWhatsappGeneral = 'https://wa.me/' . STORE_WHATSAPP . '?text=' . urlencode($mensajeWhatsappGeneral);

$linkWhatsappPedido = '';
if ($pedido) {
    // Arma el mensaje con nombre y código del pedido específico
    $mensajeWhatsappPedido = 'Hola, soy ' . (string) $pedido['cliente_nombre'] . '. Quiero consultar mi pedido ' . (string) $pedido['codigo'] . '. Gracias.';
    $linkWhatsappPedido = 'https://wa.me/' . STORE_WHATSAPP . '?text=' . urlencode($mensajeWhatsappPedido);
}
?>

<div class="table-card p-3 p-md-4 mx-auto" style="max-width: 700px;">
    <h2 class="h4 mb-1">Seguimiento de pedidos</h2>
    <p class="text-muted mb-3">Puedes buscar por código + teléfono o usar solo tu teléfono para ver tus pedidos.</p>

    <!-- Formulario principal: búsqueda por código + teléfono -->
    <form method="post" class="row g-2 mb-4">
        <input type="hidden" name="action" value="buscar_codigo">
        <div class="col-md-6">
            <label class="form-label">Código de pedido</label>
            <input
                type="text"
                name="codigo"
                class="form-control"
                placeholder="Ej: PED-250515123456789"
                value="<?= htmlspecialchars($codigoBuscado) ?>"
                required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input
                type="text"
                name="telefono"
                class="form-control"
                placeholder="Ej: 3001234567"
                value="<?= htmlspecialchars($telefonoBuscado) ?>"
                required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Buscar pedido</button>
        </div>
    </form>

    <!-- Sección secundaria: búsqueda solo por teléfono + link WhatsApp genérico -->
    <div class="border rounded-3 p-3 mb-4" style="background: #f8f9fa;">
        <h3 class="h6 mb-2">No recuerdas el código del pedido?</h3>
        <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="buscar_telefono">
            <div class="col-md-8">
                <label class="form-label">Busca con tu teléfono</label>
                <input
                    type="text"
                    name="telefono"
                    class="form-control"
                    placeholder="Ej: 3001234567"
                    value="<?= htmlspecialchars($telefonoBuscado) ?>"
                    required>
            </div>
            <div class="col-md-4 d-grid">
                <button type="submit" class="btn btn-outline-primary">Ver mis pedidos</button>
            </div>
        </form>

        <!-- Link de WhatsApp genérico para contactar la tienda -->
        <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted small">Si tu pedido se demora, puedes escribirnos directo:</span>
            <a class="btn btn-success btn-sm" href="<?= htmlspecialchars($linkWhatsappGeneral) ?>" target="_blank" rel="noopener noreferrer">WhatsApp Tienda</a>
            <span class="text-muted small">o llamar al <?= htmlspecialchars(STORE_PHONE) ?></span>
        </div>
    </div>

    <?php if ($pedidosPorTelefono): ?>
        <!-- Tabla de pedidos encontrados al buscar por teléfono -->
        <div class="mb-4">
            <h3 class="h6 mb-2">Pedidos encontrados: <?= count($pedidosPorTelefono) ?></h3>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosPorTelefono as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $p['codigo']) ?></td>
                                <td><?= htmlspecialchars((string) $p['creado_en']) ?></td>
                                <td><?= money((float) $p['total']) ?></td>
                                <td>
                                    <!-- Badge con color según el estado del pedido -->
                                    <span class="badge text-bg-<?= $estadoClase[$p['estado']] ?? 'dark' ?>">
                                        <?= htmlspecialchars($estadoNombre[$p['estado']] ?? (string) $p['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Enlace al detalle: pasa id + teléfono por GET -->
                                    <a class="btn btn-primary btn-sm" href="/tienda-barrio/seguimiento.php?id=<?= (int) $p['id'] ?>&telefono=<?= urlencode($telefonoBuscado) ?>">Ver detalle</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($pedido): ?>
        <!-- Detalle completo del pedido encontrado -->
        <div class="border-top pt-3">

            <!-- Encabezado: código y fecha del pedido -->
            <div class="p-3 mb-3 rounded-3" style="background: #f8f9fa;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Código de pedido</h6>
                        <p class="m-0 fw-semibold"><?= htmlspecialchars($pedido['codigo']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Fecha</h6>
                        <p class="m-0 fw-semibold"><?= htmlspecialchars((string) $pedido['creado_en']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Estado actual: badge coloreado + descripción amigable -->
            <h5 class="mb-3">Estado actual</h5>
            <div class="p-3 rounded-3 mb-3" style="background: rgba(15, 118, 110, 0.08); border-left: 4px solid #0f766e;">
                <p class="mb-1">
                    <span class="badge text-bg-<?= $estadoClase[$pedido['estado']] ?? 'dark' ?>" style="font-size: 0.95rem;">
                        <?= htmlspecialchars($estadoNombre[$pedido['estado']] ?? $pedido['estado']) ?>
                    </span>
                </p>
                <p class="m-0 text-muted small"><?= htmlspecialchars($estadoDescripcion[$pedido['estado']] ?? '') ?></p>
            </div>

            <!-- Datos del cliente: nombre, teléfono y dirección -->
            <h5 class="mb-3">Detalles del cliente</h5>
            <div class="p-3 mb-3 rounded-3" style="background: #f8f9fa;">
                <p class="m-0"><strong>Nombre:</strong> <?= htmlspecialchars($pedido['cliente_nombre']) ?></p>
                <p class="m-0"><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['cliente_telefono']) ?></p>
                <p class="m-0"><strong>Dirección:</strong> <?= htmlspecialchars($pedido['cliente_direccion']) ?></p>
            </div>

            <!-- Tabla de productos incluidos en el pedido -->
            <h5 class="mb-3">Productos pedidos</h5>
            <div class="table-responsive mb-3">
                <table class="table align-middle" style="font-size: 0.95rem;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
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

            <!-- Total del pedido -->
            <div class="p-3 rounded-3" style="background: #f8f9fa; border-right: 4px solid #0f766e;">
                <p class="m-0"><strong>Total del pedido: <?= money((float) $pedido['total']) ?></strong></p>
            </div>

            <!-- Acciones: volver a buscar o reportar demora por WhatsApp con código incluido -->
            <div class="mt-3">
                <a href="/tienda-barrio/seguimiento.php" class="btn btn-outline-primary">Buscar otro pedido</a>
                <?php if ($linkWhatsappPedido !== ''): ?>
                    <a href="<?= htmlspecialchars($linkWhatsappPedido) ?>" class="btn btn-success" target="_blank" rel="noopener noreferrer">Reportar demora por WhatsApp</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php';