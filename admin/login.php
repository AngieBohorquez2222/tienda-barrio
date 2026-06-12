<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if (is_admin_logged_in()) {
    redirect('/tienda-de-barrio/admin/productos.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        set_flash('danger', 'Completa usuario y contrasena.');
        redirect('/tienda-de-barrio/admin/login.php');
    }

    $stmt = db()->prepare('SELECT id, usuario, nombre, password FROM usuarios_admin WHERE usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, (string) $admin['password'])) {
        set_flash('danger', 'Credenciales incorrectas.');
        redirect('/tienda-de-barrio/admin/login.php');
    }

    $_SESSION['admin'] = [
        'id' => (int) $admin['id'],
        'usuario' => (string) $admin['usuario'],
        'nombre' => (string) $admin['nombre'],
    ];

    set_flash('success', 'Bienvenido al panel de administracion.');
    redirect('/tienda-de-barrio/admin/productos.php');
}

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
