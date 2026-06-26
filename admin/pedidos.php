<?php
/**
 * Inicio del script PHP para la gestión de pedidos en el panel de administración.
 */

// Fuerza el tipado estricto en el archivo para evitar conversiones automáticas de tipos de datos.
declare(strict_types=1);

// Incluye las funciones globales del sistema de forma obligatoria y una sola vez.
require_once __DIR__ . '/../includes/functions.php';

// Control de acceso: Verifica que la sesión pertenezca a un administrador; si no, redirige o detiene la ejecución.
require_admin_auth();

// Carga la estructura visual superior de la interfaz (HTML head, navbar, estilos).
require_once __DIR__ . '/../includes/header.php';

// Inicializa y obtiene el objeto de conexión PDO a la base de datos.
$pdo = db();

/**
 * PROCESAMIENTO DE FILTROS DESDE LA URL (MÉTODO GET)
 */

// Captura el estado del filtro, si no existe usa un string vacío, lo convierte a string y limpia espacios laterales.
$estadoFiltro = trim((string) ($_GET['estado'] ?? ''));

// Captura el término de búsqueda de la misma manera (operador de fusión de caracteres nulos '??').
$busqueda = trim((string) ($_GET['q'] ?? ''));

// Lista blanca (Whitelist) de estados válidos en el sistema para prevenir manipulaciones en la URL.
$estadosPermitidos = ['pendiente', 'preparando', 'en_camino', 'entregado', 'cancelado'];

// Validación: Si el filtro no está vacío pero NO pertenece a la lista blanca, se resetea por seguridad.
if ($estadoFiltro !== '' && !in_array($estadoFiltro, $estadosPermitidos, true)) {
    $estadoFiltro = '';
}

/**
 * CONSTRUCCIÓN DE LA CONSULTA SQL DINÁMICA
 */

// Base de la consulta para extraer los campos necesarios de la tabla 'pedidos'.
$sql = 'SELECT id, codigo, cliente_nombre, cliente_telefono, cliente_direccion, total, estado, creado_en FROM pedidos';

// Arreglos auxiliares para almacenar las condiciones WHERE y sus respectivos valores (parámetros).
$where = [];
$params = [];

// Si se definió un filtro de estado válido, se añade a la estructura de la consulta.
if ($estadoFiltro !== '') {
    $where[] = 'estado = ?'; // Marcador de posición para consulta preparada.
    $params[] = $estadoFiltro; // Valor real que ocupará el marcador.
}

// Si el usuario ingresó un término de búsqueda, se expande la condición de manera dinámica.
if ($busqueda !== '') {
    // Agrupa las condiciones con OR para buscar coincidencias en múltiples campos.
    $where[] = '(codigo LIKE ? OR cliente_nombre LIKE ? OR cliente_telefono LIKE ?)';
    
    // Configura el término de búsqueda con comodines '%' para coincidencia parcial en SQL.
    $term = '%' . $busqueda . '%';
    
    // Se añade el término tres veces (uno por cada marcador '?' de la condición anterior).
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

// Si el arreglo $where contiene elementos, significa que hay filtros activos.
if ($where) {
    // Une todas las condiciones del arreglo con un operador 'AND' lógico de SQL.
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// Modifica la consulta para ordenar los resultados de forma descendente (los más recientes primero).
$sql .= ' ORDER BY id DESC';

/**
 * EJECUCIÓN DE LA CONSULTA (PREPARED STATEMENTS - PREVIENE INYECCIÓN SQL)
 */

// Prepara la estructura de la consulta en el servidor de la base de datos.
$stmt = $pdo->prepare($sql);

// Ejecuta la consulta pasando los valores reales de forma segura, sanitizando los datos.
$stmt->execute($params);

// Recupera todas las filas resultantes como un arreglo asociativo multidimensional.
$pedidos = $stmt->fetchAll();

// Mapeo de estados del negocio con las clases de color semánticas del framework Bootstrap.
$estadoClase = [
    'pendiente'  => 'warning',   // Amarillo
    'preparando' => 'info',      // Celeste
    'en_camino'  => 'primary',   // Azul
    'entregado'  => 'success',   // Verde
    'cancelado'  => 'secondary', // Gris
];
?>

<div class="table-card p-3 p-md-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 m-0">Administración de pedidos</h2>
        <a href="/tienda-barrio/admin/productos.php" class="btn btn-outline-secondary btn-sm">
            Volver a productos
        </a>
    </div>

    <form method="get" class="row g-2 align-items-end mb-3">

        <div class="col-md-5">
            <label class="form-label">Buscar cliente, teléfono o código</label>
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
            <button type="submit" class="btn btn-primary">
                Filtrar
            </button>
            <a href="/tienda-barrio/admin/pedidos.php" class="btn btn-outline-secondary">
                Limpiar
            </a>
        </div>

    </form>

    <p class="text-muted small mb-3">
        Resultados: <?= count($pedidos) ?> pedido(s)
    </p>

    <?php if (!$pedidos): ?>

        <p class="text-muted m-0">
            No hay pedidos para el filtro seleccionado.
        </p>

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
                        <th>Acciones</th> </tr>
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
                                <small class="text-muted">
                                    <?= htmlspecialchars($pedido['cliente_direccion']) ?>
                                </small>
                            </td>

                            <td><?= money((float) $pedido['total']) ?></td>

                            <td>
                                <span class="badge text-bg-<?= $estadoClase[$pedido['estado']] ?? 'dark' ?>">
                                    <?= strtoupper(str_replace('_', ' ', $pedido['estado'])) ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars((string) $pedido['creado_en']) ?></td>

                            <td>
                                <a href="/tienda-barrio/admin/pedido_detalle.php?id=<?= (int) $pedido['id'] ?>" class="btn btn-primary btn-sm">
                                    Gestionar
                                </a>
                            </td>

                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>

        </div> <?php endif; ?> </div> ```