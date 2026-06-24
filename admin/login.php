<?php
// Activa el modo estricto de tipos para mayor seguridad
declare(strict_types=1);

// Incluye las funciones auxiliares del proyecto
require_once __DIR__ . '/../includes/functions.php';

// Si el admin ya está logueado, redirige al panel de productos
if (is_admin_logged_in()) {
    redirect('/tienda-barrio/admin/productos.php');
}

// Procesa el formulario de login cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene usuario y contraseña del formulario
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    // Valida que ambos campos estén completos
    if ($usuario === '' || $password === '') {
        set_flash('danger', 'Completa usuario y contrasena.');
        redirect('/tienda-barrio/admin/login.php');
    }

    // Busca el administrador en la tabla usuarios_admin
    $stmt = db()->prepare('SELECT id, usuario, nombre, password FROM usuarios_admin WHERE usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    $admin = $stmt->fetch();

    // Verifica que exista y la contraseña coincida (hash)
    if (!$admin || !password_verify($password, (string) $admin['password'])) {
        set_flash('danger', 'Credenciales incorrectas.');
        redirect('/tienda-barrio/admin/login.php');
    }

    // Almacena datos del admin en sesión para mantener login
    $_SESSION['admin'] = [
        'id' => (int) $admin['id'],
        'usuario' => (string) $admin['usuario'],
        'nombre' => (string) $admin['nombre'],
    ];

    // Mensaje de bienvenida y redirección al panel
    set_flash('success', 'Bienvenido al panel de administracion.');
    redirect('/tienda-barrio/admin/productos.php');
}

// Incluye el header con navbar y estructura HTML básica
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card p-3 p-md-4 mx-auto" style="max-width: 500px;">
    <h2 class="h4 mb-1">Inicio de sesion admin</h2>
    <p class="text-muted mb-3">Accede para crear, editar y eliminar productos.</p>

    <form method="post" class="row g-3">
        <div class="col-12">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" maxlength="60" required>
        </div>
        <div class="col-12">
            <label class="form-label">Contrasena</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-12 d-grid">
            <button class="btn btn-warning" type="submit">Ingresar</button>
        </div>
    </form>

    <small class="text-muted d-block mt-3">
        Usuario inicial: <strong>admin</strong> | Contrasena inicial: <strong>admin123</strong>
    </small>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';
