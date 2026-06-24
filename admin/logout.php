<?php
// Activa el modo estricto de tipos para mayor seguridad
declare(strict_types=1);

// Incluye las funciones auxiliares del proyecto
require_once __DIR__ . '/../includes/functions.php';

// Elimina la sesión del administrador actual
unset($_SESSION['admin']);
// Muestra mensaje informativo de cierre de sesión
set_flash('info', 'Sesion cerrada correctamente.');
// Redirige a la página de login del panel admin
redirect('/tienda-barrio/admin/login.php');
