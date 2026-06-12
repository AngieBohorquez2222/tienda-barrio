<?php
// Activa el modo estricto de tipos para seguridad
declare(strict_types=1);

// Incluye el archivo de configuración principal
require_once __DIR__ . '/../config.php';

// Formatea un número como moneda colombiana (ej: $1.234)
function money(float $value): string
{
    // Agrega símbolo $ y formatea con puntos como separador de miles
    return '$' . number_format($value, 0, ',', '.');
}

// Almacena un mensaje flash en la sesión para mostrar una sola vez
function set_flash(string $type, string $message): void
{
    // Guarda tipo (danger, success, warning, etc) y mensaje en sesión
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

// Obtiene y elimina el mensaje flash de la sesión
function get_flash(): ?array
{
    // Si no existe mensaje flash, retorna null
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    // Guarda el mensaje y lo elimina de la sesión (solo se muestra una vez)
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

// Redirige a una URL y detiene la ejecución
function redirect(string $path): void
{
    // Envía header de redirección
    header('Location: ' . $path);
    exit;
}

// Verifica si el administrador está logueado
function is_admin_logged_in(): bool
{
    // Retorna true si existe la variable de sesión admin como array
    return isset($_SESSION['admin']) && is_array($_SESSION['admin']);
}

// Verifica si el usuario está logueado
function is_user_logged_in(): bool
{
    // Retorna true si existe la variable de sesión user como array
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

// Obtiene el nombre del administrador logueado
function admin_name(): string
{
    // Si no está logueado, retorna vacío
    if (!is_admin_logged_in()) {
        return '';
    }

    // Retorna el nombre desde la sesión
    return (string) ($_SESSION['admin']['nombre'] ?? '');
}

// Obtiene el nombre del usuario logueado
function user_name(): string
{
    // Si no está logueado, retorna vacío
    if (!is_user_logged_in()) {
        return '';
    }

    // Retorna el nombre desde la sesión
    return (string) ($_SESSION['user']['nombre'] ?? '');
}

// Requiere autenticación de administrador o redirige al login
function require_admin_auth(): void
{
    // Si ya está logueado, no hace nada
    if (is_admin_logged_in()) {
        return;
    }

    // Si no está logueado, muestra mensaje y redirige al login
    set_flash('warning', 'Debes iniciar sesion para administrar productos.');
    redirect('/tienda-de-barrio/admin/login.php');
}

// Sube una imagen de producto al servidor
function upload_product_image(string $fieldName = 'imagen_file'): ?string
{
    // Verifica si el archivo fue enviado
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    // Obtiene datos del archivo
    $file = $_FILES[$fieldName];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    // Si no se seleccionó archivo, retorna null
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    // Si hay error en la subida, lanza excepción
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo cargar la imagen del producto.');
    }

    // Obtiene la ruta temporal y el tipo MIME
    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $mime = (string) mime_content_type($tmpPath);
    
    // Extensiones permitidas según MIME type
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
    ];

    // Si el MIME no está permitido, lanza excepción
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato no permitido. Usa JPG, PNG, WEBP o SVG.');
    }

    // Crea el directorio de uploads si no existe
    $uploadDir = __DIR__ . '/../img/productos/uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('No fue posible crear la carpeta de imagenes.');
    }

    // Genera nombre único para el archivo
    $fileName = 'prod_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $targetPath = $uploadDir . '/' . $fileName;

    // Mueve el archivo al directorio final
    if (!move_uploaded_file($tmpPath, $targetPath)) {
        throw new RuntimeException('No fue posible guardar la imagen cargada.');
    }

    // Retorna la URL pública de la imagen
    return '/tienda-de-barrio/img/productos/uploads/' . $fileName;
}

// Envía notificación por WhatsApp al cambiar estado del pedido
function send_whatsapp_notification(int $pedidoId, string $estadoNuevo, string $estadoAnterior, string $telefono, string $clienteNombre): bool
{
    $pdo = db();

    // Mapeo de estados a nombres legibles
    $estadosNombre = [
        'pendiente' => 'Pendiente',
        'preparando' => 'Preparando',
        'en_camino' => 'En camino',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    // Plantillas de mensajes según el estado nuevo
    $mensajePlantilla = [
        'preparando' => "¡Hola {nombre}! Tu pedido está siendo preparado. Pronto te enviaremos más novedades.",
        'en_camino' => "¡Tu pedido está en camino! Llegaré pronto a tu domicilio.",
        'entregado' => "¡Tu pedido fue entregado! Gracias por tu compra en Mercadito La Esquina.",
        'cancelado' => "Tu pedido ha sido cancelado. Por favor contactanos para más información.",
    ];

    // Reemplaza {nombre} por el nombre del cliente en el mensaje
    $mensaje = str_replace('{nombre}', $clienteNombre, $mensajePlantilla[$estadoNuevo] ?? '');

    try {
        // Inserta registro en tabla notificaciones
        $insertNotif = $pdo->prepare(
            'INSERT INTO notificaciones (pedido_id, tipo, estado_anterior, estado_nuevo, telefono, mensaje, enviado) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $insertNotif->execute([$pedidoId, 'whatsapp', $estadoAnterior, $estadoNuevo, $telefono, $mensaje, false]);
        $notifId = (int) $pdo->lastInsertId();

        // Aqui va la integracion con API real de WhatsApp (Click, Twilio, etc)
        // Por ahora registramos como "enviado" para fines educativos
        $update = $pdo->prepare('UPDATE notificaciones SET enviado = ? WHERE id = ?');
        $update->execute([true, $notifId]);

        return true;
    } catch (Throwable $e) {
        return false;
    }
}