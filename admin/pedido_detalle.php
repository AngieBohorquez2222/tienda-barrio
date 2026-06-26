<?php
// Activa el modo estricto de tipos para evitar conversiones automáticas de datos
declare(strict_types=1);

// Incluye el archivo que contiene las funciones generales del proyecto
require_once __DIR__ . '/../includes/functions.php';

// Verifica que el usuario sea administrador; si no lo es, lo redirige al inicio de sesión
require_admin_auth();

// Obtiene la conexión a la base de datos mediante PDO
$pdo = db();

// Obtiene el ID del pedido desde la URL (GET) o desde el formulario (POST)
// Si no existe, asigna el valor 0
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

// Comprueba que el ID sea válido
if ($id <= 0) {
    // Guarda un mensaje de error para mostrarlo al usuario
    set_flash('danger', 'Pedido no valido.');

    // Redirecciona a la lista de pedidos
    redirect('/tienda-barrio/admin/pedidos.php');
}

// Define los estados permitidos para un pedido
$estadosPermitidos = [
    'pendiente',
    'preparando',
    'en_camino',
    'entregado',
    'cancelado'
];

// Comprueba si el formulario fue enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtiene el nuevo estado seleccionado en el formulario
    $estado = $_POST['estado'] ?? '';

    // Verifica que el estado recibido sea válido
    if (!in_array($estado, $estadosPermitidos, true)) {

        // Muestra un mensaje de error
        set_flash('danger', 'Estado no permitido.');

        // Regresa al detalle del pedido
        redirect('/tienda-barrio/admin/pedido_detalle.php?id=' . $id);
    }

    // Consulta el estado actual y los datos del cliente
    $stmtEstadoActual = $pdo->prepare(
        'SELECT estado, cliente_nombre, cliente_telefono FROM pedidos WHERE id = ?'
    );

    // Ejecuta la consulta usando el ID del pedido
    $stmtEstadoActual->execute([$id]);

    // Obtiene el resultado de la consulta
    $estadoActual = $stmtEstadoActual->fetch();

    // Prepara la consulta para actualizar el estado
    $update = $pdo->prepare(
        'UPDATE pedidos SET estado = ? WHERE id = ?'
    );

    // Ejecuta la actualización
    $update->execute([$estado, $id]);

    // Envía un mensaje por WhatsApp notificando el cambio de estado
    send_whatsapp_notification(
        $id,
        $estado,
        (string) $estadoActual['estado'],
        (string) $estadoActual['cliente_telefono'],
        (string) $estadoActual['cliente_nombre']
    );

    // Guarda un mensaje de éxito
    set_flash(
        'success',
        'Estado actualizado y notificación enviada por WhatsApp.'
    );

    // Recarga la página para mostrar la información actualizada
    redirect('/tienda-barrio/admin/pedido_detalle.php?id=' . $id);
}

// Consulta la información principal del pedido
$stmtPedido = $pdo->prepare(
    'SELECT id, codigo, cliente_nombre, cliente_telefono,
    cliente_direccion, total, estado, creado_en
    FROM pedidos
    WHERE id = ?'
);

// Ejecuta la consulta
$stmtPedido->execute([$id]);

// Obtiene los datos del pedido
$pedido = $stmtPedido->fetch();

// Comprueba si el pedido existe
if (!$pedido) {

    // Muestra un mensaje de error
    set_flash('danger', 'Pedido no encontrado.');

    // Regresa a la lista de pedidos
    redirect('/tienda-barrio/admin/pedidos.php');
}

// Consulta todos los productos pertenecientes al pedido
$stmtItems = $pdo->prepare(
    'SELECT nombre_producto,
            precio_unitario,
            cantidad,
            subtotal
     FROM pedido_items
     WHERE pedido_id = ?
     ORDER BY id'
);

// Ejecuta la consulta
$stmtItems->execute([$id]);

// Obtiene todos los productos encontrados
$items = $stmtItems->fetchAll();

// Relaciona cada estado con un color Bootstrap
$estadoClase = [
    'pendiente' => 'warning',
    'preparando' => 'info',
    'en_camino' => 'primary',
    'entregado' => 'success',
    'cancelado' => 'secondary',
];

// Relaciona cada estado con su nombre legible
$estadoNombre = [
    'pendiente' => 'Pendiente',
    'preparando' => 'Preparando',
    'en_camino' => 'En camino',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];

// Incluye la cabecera del sitio
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Tarjeta principal que contiene toda la información del pedido -->
<div class="table-card p-3 p-md-4 mb-4">

    <!-- Encabezado con código del pedido y botón para regresar -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <!-- Muestra el código del pedido -->
        <h2 class="h4 m-0">
            Pedido <?= htmlspecialchars($pedido['codigo']) ?>
        </h2>

        <!-- Botón para volver al listado -->
        <a href="/tienda-barrio/admin/pedidos.php"
           class="btn btn-outline-secondary btn-sm">
            Volver
        </a>
    </div>

    <!-- Información general organizada en tres columnas -->
    <div class="row g-3 mb-3">

        <!-- Información del cliente -->
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">

                <h3 class="h6 text-muted">Cliente</h3>

                <!-- Nombre del cliente -->
                <p class="m-0 fw-semibold">
                    <?= htmlspecialchars($pedido['cliente_nombre']) ?>
                </p>

                <!-- Teléfono -->
                <p class="m-0">
                    <?= htmlspecialchars($pedido['cliente_telefono']) ?>
                </p>

                <!-- Dirección -->
                <p class="m-0 text-muted">
                    <?= htmlspecialchars($pedido['cliente_direccion']) ?>
                </p>

            </div>
        </div>

        <!-- Información del pedido -->
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">

                <h3 class="h6 text-muted">Resumen</h3>

                <!-- Total del pedido -->
                <p class="m-0">
                    Total:
                    <strong><?= money((float) $pedido['total']) ?></strong>
                </p>

                <!-- Fecha de creación -->
                <p class="m-0">
                    Creado:
                    <?= htmlspecialchars((string) $pedido['creado_en']) ?>
                </p>

            </div>
        </div>

        <!-- Estado actual y formulario -->
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">

                <h3 class="h6 text-muted">Estado actual</h3>

                <!-- Badge que muestra el estado -->
                <p class="mb-2">
                    <span class="badge text-bg-<?= $estadoClase[$pedido['estado']] ?? 'dark' ?>">

                        <!-- Nombre legible del estado -->
                        <?= htmlspecialchars($estadoNombre[$pedido['estado']] ?? $pedido['estado']) ?>

                    </span>
                </p>

                <!-- Formulario para actualizar el estado -->
                <form method="post" class="d-flex gap-2">

                    <!-- Envía el ID oculto -->
                    <input type="hidden"
                           name="id"
                           value="<?= (int) $pedido['id'] ?>">

                    <!-- Lista desplegable de estados -->
                    <select name="estado"
                            class="form-select form-select-sm"
                            required>

                        <!-- Recorre todos los estados disponibles -->
                        <?php foreach ($estadosPermitidos as $estado): ?>

                            <!-- Marca como seleccionado el estado actual -->
                            <option
                                value="<?= $estado ?>"
                                <?= $pedido['estado'] === $estado ? 'selected' : '' ?>>

                                <?= htmlspecialchars($estadoNombre[$estado]) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <!-- Botón para guardar -->
                    <button type="submit"
                            class="btn btn-primary btn-sm">
                        Guardar
                    </button>

                </form>

            </div>
        </div>
    </div>

    <!-- Título de la tabla -->
    <h3 class="h5">Detalle del pedido</h3>

    <!-- Hace la tabla adaptable a dispositivos móviles -->
    <div class="table-responsive">

        <table class="table align-middle">

            <!-- Encabezados -->
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio unidad</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <!-- Cuerpo de la tabla -->
            <tbody>

                <!-- Recorre todos los productos del pedido -->
                <?php foreach ($items as $item): ?>

                    <tr>

                        <!-- Nombre del producto -->
                        <td><?= htmlspecialchars($item['nombre_producto']) ?></td>

                        <!-- Precio unitario -->
                        <td><?= money((float) $item['precio_unitario']) ?></td>

                        <!-- Cantidad -->
                        <td><?= (int) $item['cantidad'] ?></td>

                        <!-- Subtotal -->
                        <td><?= money((float) $item['subtotal']) ?></td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<<<<<<< Updated upstream
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
=======
<?php
// Incluye el pie de página del sitio
require_once __DIR__ . '/../includes/footer.php';
?>
>>>>>>> Stashed changes
