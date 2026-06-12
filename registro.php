<?php
// Activa el modo estricto de tipos para seguridad
declare(strict_types=1);

// Incluye funciones auxiliares
require_once __DIR__ . '/includes/functions.php';

// Si ya está logueado, redirige al catálogo
if (is_user_logged_in()) {
    redirect('/tienda-de-barrio/index.php');
}

// Procesa registro cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene todos los campos del formulario
    $usuario = trim($_POST['usuario'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    // Validación: todos los campos deben estar completos
    if ($usuario === '' || $nombre === '' || $email === '' || $telefono === '' || $direccion === '' || $password === '') {
        set_flash('danger', 'Completa todos los campos.');
        redirect('/tienda-de-barrio/registro.php');
    }

    // Validación: las contraseñas deben coincidir
    if ($password !== $confirmar) {
        set_flash('danger', 'Las contrasenas no coinciden.');
        redirect('/tienda-de-barrio/registro.php');
    }

    // Validación: contraseña mínima 6 caracteres
    if (strlen($password) < 6) {
        set_flash('danger', 'La contrasena debe tener al menos 6 caracteres.');
        redirect('/tienda-de-barrio/registro.php');
    }

    // Verifica que el usuario no exista ya
    $stmt = db()->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    if ($stmt->fetch()) {
        set_flash('danger', 'El nombre de usuario ya esta registrado.');
        redirect('/tienda-de-barrio/registro.php');
    }

    // Verifica que el email no exista ya
    $stmt = db()->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        set_flash('danger', 'El email ya esta registrado.');
        redirect('/tienda-de-barrio/registro.php');
    }

    // Encripta la contraseña con password_hash
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Inserta el nuevo usuario en la base de datos
    $stmt = db()->prepare('INSERT INTO usuarios (usuario, nombre, email, telefono, direccion, password) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$usuario, $nombre, $email, $telefono, $direccion, $hashedPassword]);

    // Mensaje de éxito y redirección al login
    set_flash('success', 'Usuario registrado exitosamente. Ahora puedes iniciar sesion.');
    redirect('/tienda-de-barrio/login.php');
}

// Incluye el header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Tarjeta de registro centrada -->
<div class="form-card p-3 p-md-4 mx-auto" style="max-width: 500px;">
    <!-- Título del formulario -->
    <h2 class="h4 mb-1">Registro de Usuario</h2>
    <!-- Descripción -->
    <p class="text-muted mb-3">Crea tu cuenta para realizar pedidos.</p>

    <!-- Formulario de registro -->
    <form method="post" class="row g-3">
        <!-- Campo usuario -->
        <div class="col-12">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control" maxlength="60" required>
        </div>
        <!-- Campo nombre completo -->
        <div class="col-12">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre" class="form-control" maxlength="120" required>
        </div>
        <!-- Campo email -->
        <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" maxlength="120" required>
        </div>
        <!-- Campo teléfono -->
        <div class="col-12">
            <label class="form-label">Telefono</label>
            <input type="tel" name="telefono" class="form-control" maxlength="30" required>
        </div>
        <!-- Campo dirección -->
        <div class="col-12">
            <label class="form-label">Direccion</label>
            <input type="text" name="direccion" class="form-control" maxlength="180" required>
        </div>
        <!-- Campo contraseña -->
        <div class="col-12">
            <label class="form-label">Contrasena</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <!-- Campo confirmar contraseña -->
        <div class="col-12">
            <label class="form-label">Confirmar Contrasena</label>
            <input type="password" name="confirmar" class="form-control" required>
        </div>
        <!-- Botón submit -->
        <div class="col-12 d-grid">
            <button class="btn btn-warning" type="submit">Registrarse</button>
        </div>
    </form>

    <!-- Enlace a login -->
    <small class="text-muted d-block mt-3">
        ¿Ya tienes cuenta? <a href="/tienda-de-barrio/login.php">Inicia sesion aqui</a>
    </small>
</div>

<!-- Footer -->
<?php require_once __DIR__ . '/includes/footer.php';