<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

// ============================================================
// INICIALIZACIÓN DE VARIABLES
// ============================================================

$pdo = db(); // Conexión a la base de datos
$cart = $_SESSION['cart'] ?? []; // Carrito en sesión: [producto_id => cantidad]

// Pre-rellena el form con datos del usuario logueado o con lo que
// escribió antes en caso de error (guardado en sesión tras fallo).
$checkoutForm = $_SESSION['checkout_form'] ?? [
    'cliente_nombre' => is_user_logged_in() ? ($_SESSION['user']['nombre'] ?? '') : '',
    'cliente_telefono' => is_user_logged_in() ? ($_SESSION['user']['telefono'] ?? '') : '',
    'cliente_direccion' => is_user_logged_in() ? ($_SESSION['user']['direccion'] ?? '') : '',
];

// ============================================================
// MANEJO DE FORMULARIOS POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ------------------------------------------------------------
    // ACCIÓN: actualizar cantidad de un producto en el carrito
    // ------------------------------------------------------------
    if ($action === 'update') {
        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1)); // Mínimo permitido: 1

        // Verifica que el producto exista en la BD antes de actualizar
        $stmt = $pdo->prepare('SELECT stock FROM productos WHERE id = ?');
        $stmt->execute([$productoId]);
        $producto = $stmt->fetch();

        if (!$producto) {
            set_flash('danger', 'El producto no existe.');
            redirect('/tienda-barrio/carrito.php');
        }

        // No permite pedir más unidades de las que hay en stock
        if ($cantidad > (int) $producto['stock']) {
            set_flash('warning', 'La cantidad supera el stock disponible.');
            redirect('/tienda-barrio/carrito.php');
        }

        // Actualiza la cantidad en sesión y recarga la página
        $_SESSION['cart'][$productoId] = $cantidad;
        set_flash('success', 'Cantidad actualizada.');
        redirect('/tienda-barrio/carrito.php');
    }

    // ------------------------------------------------------------
    // ACCIÓN: eliminar un producto del carrito
    // ------------------------------------------------------------
    if ($action === 'remove') {
        $productoId = (int) ($_POST['producto_id'] ?? 0);
        unset($_SESSION['cart'][$productoId]); // Borra la clave del array en sesión
        set_flash('info', 'Producto removido del carrito.');
        redirect('/tienda-barrio/carrito.php');
    }

    // ------------------------------------------------------------
    // ACCIÓN: finalizar compra (checkout)
    // ------------------------------------------------------------
    if ($action === 'checkout') {
        $cart = $_SESSION['cart'] ?? [];
        $clienteNombre = trim($_POST['cliente_nombre'] ?? '');
        $clienteTelefono = trim($_POST['cliente_telefono'] ?? '');
        $clienteDireccion = trim($_POST['cliente_direccion'] ?? '');

        // Guarda los datos del formulario en sesión para repoblarlos si hay error
        $_SESSION['checkout_form'] = [
            'cliente_nombre' => $clienteNombre,
            'cliente_telefono' => $clienteTelefono,
            'cliente_direccion' => $clienteDireccion,
        ];

        // Validación: el carrito no debe estar vacío
        if (!$cart) {
            set_flash('warning', 'No hay productos en el carrito.');
            redirect('/tienda-barrio/carrito.php');
        }

        // Validación: todos los campos del cliente son obligatorios
        if ($clienteNombre === '' || $clienteTelefono === '' || $clienteDireccion === '') {
            set_flash('warning', 'Completa nombre, telefono y direccion para crear el pedido.');
            redirect('/tienda-barrio/carrito.php');
        }

        try {
            // Inicia transacción: garantiza que todo se guarda o nada (atomicidad)
            $pdo->beginTransaction();
            $productosPedido = [];
            $totalPedido = 0.0;

            // Recorre el carrito validando stock y calculando totales
            foreach ($cart as $id => $qty) {
                // FOR UPDATE bloquea la fila durante la transacción,
                // evitando que dos compras simultáneas consuman el mismo stock
                $stmt = $pdo->prepare('SELECT id, nombre, precio, stock FROM productos WHERE id = ? FOR UPDATE');
                $stmt->execute([(int) $id]);
                $producto = $stmt->fetch();

                // Si el producto no existe o el stock es insuficiente, aborta todo
                if (!$producto || (int) $producto['stock'] < (int) $qty) {
                    throw new RuntimeException('Stock insuficiente para finalizar la compra.');
                }

                $cantidad = (int) $qty;
                $precioUnitario = (float) $producto['precio'];
                $subtotal = $cantidad * $precioUnitario;
                $totalPedido += $subtotal;

                // Acumula los ítems validados para insertarlos después
                $productosPedido[] = [
                    'id' => (int) $producto['id'],
                    'nombre' => (string) $producto['nombre'],
                    'precio' => $precioUnitario,
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal,
                ];
            }

            // Genera código único: PED-AAMMDDHHMMSS-NNN
            // ⚠️ Con tráfico alto pueden generarse colisiones; considerar UUID
            $codigoPedido = 'PED-' . date('ymdHis') . '-' . random_int(100, 999);

            // Inserta el encabezado del pedido en la tabla `pedidos`
            $insertPedido = $pdo->prepare(
                'INSERT INTO pedidos (codigo, cliente_nombre, cliente_telefono, cliente_direccion, total, estado) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $insertPedido->execute([$codigoPedido, $clienteNombre, $clienteTelefono, $clienteDireccion, $totalPedido, 'pendiente']);
            $pedidoId = (int) $pdo->lastInsertId(); // ID autoincremental del pedido recién creado

            // Prepara los statements de inserción de ítems y descuento de stock
            $insertItem = $pdo->prepare(
                'INSERT INTO pedido_items (pedido_id, producto_id, nombre_producto, precio_unitario, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)'
            );

            $update = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');

            // Inserta cada ítem y descuenta el stock correspondiente
            foreach ($productosPedido as $item) {
                $insertItem->execute([
                    $pedidoId,
                    $item['id'],
                    $item['nombre'],
                    $item['precio'],
                    $item['cantidad'],
                    $item['subtotal'],
                ]);

                $update->execute([$item['cantidad'], $item['id']]);
            }

            // Confirma todos los cambios en la BD
            $pdo->commit();

            // Limpia el carrito y el formulario guardado en sesión
            $_SESSION['cart'] = [];
            unset($_SESSION['checkout_form']);
            set_flash('success', 'Pedido ' . $codigoPedido . ' creado con exito. El administrador ya puede gestionarlo.');
        } catch (Throwable $e) {
            // Algo falló: revierte todos los cambios de la transacción
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            set_flash('danger', $e->getMessage());
        }

        redirect('/tienda-barrio/carrito.php');
    }
}

// ============================================================
// CONSTRUCCIÓN DE LA VISTA DEL CARRITO
// Se recarga desde sesión (puede haber cambiado arriba) y se
// consultan los datos actuales de cada producto en la BD.
// ============================================================

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0.0;

if ($cart) {
    // Obtiene todos los productos del carrito en una sola consulta (IN)
    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $producto) {
        $qty = (int) ($cart[(int) $producto['id']] ?? 0);
        if ($qty <= 0) {
            continue; // Ignora entradas inválidas que puedan quedar en sesión
        }
        $subtotal = $qty * (float) $producto['precio'];
        $total += $subtotal;

        // Construye el array de ítems para renderizar la tabla
        $items[] = [
            'id' => (int) $producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => (float) $producto['precio'],
            'stock' => (int) $producto['stock'],
            'qty' => $qty,
            'subtotal' => $subtotal,
        ];
    }
}
?>

<div class="table-card p-3 p-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 m-0">Carrito de compras</h2>
        <a href="/tienda-barrio/index.php" class="btn btn-outline-primary btn-sm">Seguir comprando</a>
    </div>

    <?php if (!$items): ?>
        <p class="text-muted m-0">Tu carrito esta vacio.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($item['nombre']) ?><br>
                                <!-- Stock disponible mostrado como referencia al usuario -->
                                <small class="text-muted">Stock actual: <?= $item['stock'] ?></small>
                            </td>
                            <td><?= money($item['precio']) ?></td>
                            <td>
                                <!-- Formulario de actualización: se envía al cambiar qty o pulsar +/- -->
                                <form method="post" class="update-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="producto_id" value="<?= $item['id'] ?>">
                                    <div class="qty-control">
                                        <button class="btn btn-outline-secondary btn-sm qty-btn" type="button" data-action="minus">-</button>
                                        <input
                                            class="form-control form-control-sm qty-input"
                                            type="number"
                                            name="cantidad"
                                            min="1"
                                            max="<?= $item['stock'] ?>"
                                            value="<?= $item['qty'] ?>"
                                            required>
                                        <button class="btn btn-outline-secondary btn-sm qty-btn" type="button" data-action="plus">+</button>
                                    </div>
                                </form>
                            </td>
                            <td><?= money($item['subtotal']) ?></td>
                            <td>
                                <!-- Formulario independiente para quitar el producto del carrito -->
                                <form method="post">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="producto_id" value="<?= $item['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th><?= money($total) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Formulario de checkout: datos del cliente para crear el pedido -->
        <form method="post" class="mt-3">
            <input type="hidden" name="action" value="checkout">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Nombre del cliente</label>
                    <input
                        type="text"
                        name="cliente_nombre"
                        class="form-control"
                        maxlength="120"
                        value="<?= htmlspecialchars((string) $checkoutForm['cliente_nombre']) ?>"
                        required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefono</label>
                    <input
                        type="text"
                        name="cliente_telefono"
                        class="form-control"
                        maxlength="30"
                        value="<?= htmlspecialchars((string) $checkoutForm['cliente_telefono']) ?>"
                        required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Direccion de entrega</label>
                    <input
                        type="text"
                        name="cliente_direccion"
                        class="form-control"
                        maxlength="180"
                        value="<?= htmlspecialchars((string) $checkoutForm['cliente_direccion']) ?>"
                        required>
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-success">Crear pedido</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    // Control de cantidad con botones +/-
    // Cada fila tiene su propio formulario .update-form.
    // Los botones modifican el input y envían el formulario automáticamente.
    document.querySelectorAll('.update-form').forEach(function(form) {
        var input = form.querySelector('.qty-input');
        var min = Number(input.min);
        var max = Number(input.max);

        form.querySelectorAll('.qty-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var value = Number(input.value);
                if (btn.dataset.action === 'minus') {
                    value = Math.max(min, value - 1); // No baja del mínimo (1)
                } else {
                    value = Math.min(max, value + 1); // No sube del stock disponible
                }
                input.value = value;
                form.submit(); // Envía el form para persistir el cambio en sesión
            });
        });

        // También envía si el usuario escribe directamente en el input
        input.addEventListener('change', function() {
            form.submit();
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php';