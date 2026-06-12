<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['admin']);
set_flash('info', 'Sesion cerrada correctamente.');
redirect('/tienda-de-barrio/admin/login.php');
