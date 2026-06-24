<?php
// Activa el modo estricto de tipos para seguridad
declare(strict_types=1);

// Incluye funciones auxiliares
require_once __DIR__ . '/includes/functions.php';

// Si ya está logueado, redirige al catálogo
if (is_user_logged_in()) {
    redirect('/tienda-barrio/index.php');
}

// Procesa login cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene usuario y contraseña del formulario
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    // Valida que ambos campos estén completos
    if ($usuario === '' || $password === '') {
        set_flash('danger', 'Completa usuario y contrasena.');
        redirect('/tienda-barrio/login.php');
    }

    // Busca el usuario en la base de datos
    $stmt = db()->prepare('SELECT id, usuario, nombre, telefono, direccion, password FROM usuarios WHERE usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    // Verifica que exista y la contraseña coincida (hash)
    if (!$user || !password_verify($password, (string) $user['password'])) {
        set_flash('danger', 'Credenciales incorrectas.');
        redirect('/tienda-barrio/login.php');
    }

    // Guarda datos en sesión para mantener login
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'usuario' => (string) $user['usuario'],
        'nombre' => (string) $user['nombre'],
        'telefono' => (string) $user['telefono'],
        'direccion' => (string) $user['direccion'],
    ];

    // Mensaje de bienvenida y redirección
    set_flash('success', 'Bienvenido, ' . $user['nombre'] . '!');
    redirect('/tienda-barrio/index.php');
}

// Incluye el header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Tarjeta de login centrada -->
<div class="form-card p-3 p-md-4 mx-auto" style="max-width: 500px;">
    <!-- Título del formulario -->
    <h2 class="h4 mb-1">Iniciar Sesion</h2>
    <!-- Descripción -->
    <p class="text-muted mb-3">Accede a tu cuenta para realizar pedidos.</p>

    <!-- Formulario de login -->
    <form method="post" class="row g-3">
        <!-- Campo usuario -->
        <div class="col-12">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" maxlength="60" required>
        </div>
        <!-- Campo contraseña -->
        <div class="col-12">
            <label class="form-label">Contrasena</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <!-- Botón submit -->
        <div class="col-12 d-grid">
            <button class="btn btn-warning" type="submit">Ingresar</button>
        </div>
    </form>

    <!-- Enlace a registro -->
    <small class="text-muted d-block mt-3">
        ¿No tienes cuenta? <a href="/tienda-barrio/registro.php">Registrate aqui</a>
    </small>
</div>

<!-- Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>