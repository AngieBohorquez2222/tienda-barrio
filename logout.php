<?php
// Activa el modo estricto de tipos para seguridad
declare(strict_types=1);

// Incluye funciones auxiliares
require_once __DIR__ . '/includes/functions.php';

// Elimina la variable de sesión del usuario
unset($_SESSION['user']);

// Mensaje de confirmación
set_flash('success', 'Has cerrado sesion exitosamente.');
// Redirige al catálogo
redirect('/tienda-de-barrio/index.php');