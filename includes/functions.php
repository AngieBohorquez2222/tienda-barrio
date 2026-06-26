<?php
// Activa el modo estricto de tipos para evitar conversiones implícitas y mejorar la seguridad
declare(strict_types=1);

// Incluye el archivo de configuración principal subiendo un nivel desde el directorio actual
require_once __DIR__ . '/../config.php';

// Formatea un número decimal o entero como moneda colombiana (ej: $1.234)
function money(float $value): string
{
    // Usa number_format con 0 decimales, coma para miles y punto para decimales (estilo COP), añade el símbolo $ al inicio y lo retorna
    return '$' . number_format($value, 0, ',', '.');
}

// Almacena un mensaje temporal (flash) en la sesión global para mostrarlo una sola vez en la interfaz
function set_flash(string $type, string $message): void
{
    // Crea o sobreescribe un array asociativo dentro del índice 'flash' de la variable de sesión $_SESSION
    $_SESSION['flash'] = [
        'type' => $type,       // Define el tipo de alerta (ej: 'danger', 'success', 'warning')
        'message' => $message, // Define el texto del mensaje que verá el usuario
    ];
}

// Obtiene el mensaje flash de la sesión y lo elimina inmediatamente para que no se repita
function get_flash(): ?array
{
    // Verifica si la variable de sesión 'flash' no está definida en el sistema
    if (!isset($_SESSION['flash'])) {
        // Retorna un valor nulo si no hay mensajes guardados
        return null;
    }

    // Copia el contenido del mensaje flash actual en una variable local
    $flash = $_SESSION['flash'];
    // Borra de forma definitiva el mensaje de la sesión global mediante la función unset
    unset($_SESSION['flash']);

    // Retorna el array con el mensaje que fue rescatado
    return $flash;
}

// Redirige al navegador a una ruta específica del sitio y detiene la carga del script
function redirect(string $path): void
{
    // Envía un encabezado HTTP crudo al navegador indicando la nueva localización de destino
    header('Location: ' . $path);
    // Detiene inmediatamente la ejecución de cualquier código posterior por seguridad
    exit;
}

// Verifica si el administrador ha iniciado sesión correctamente
function is_admin_logged_in(): bool
{
    // Retorna verdadero únicamente si existe el índice 'admin' en la sesión y este contiene un array de datos
    return isset($_SESSION['admin']) && is_array($_SESSION['admin']);
}

// Verifica si un cliente/usuario general ha iniciado sesión correctamente
function is_user_logged_in(): bool
{
    // Retorna verdadero únicamente si existe el índice 'user' en la sesión y este contiene un array de datos
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

// Obtiene el nombre del administrador que se encuentra autenticado
function admin_name(): string
{
    // Si la función de validación de administrador retorna falso, deniega el proceso
    if (!is_admin_logged_in()) {
        // Devuelve una cadena de texto completamente vacía
        return '';
    }

    // Intenta leer el campo 'nombre' del admin en la sesión; si no existe, usa el operador de fusión nula (??) para devolver vacío y lo fuerza a string
    return (string) ($_SESSION['admin']['nombre'] ?? '');
}

// Obtiene el nombre del usuario común que se encuentra autenticado
function user_name(): string
{
    // Si la función de validación de usuario retorna falso, deniega el proceso
    if (!is_user_logged_in()) {
        // Devuelve una cadena de texto completamente vacía
        return '';
    }

    // Intenta leer el campo 'nombre' del usuario en la sesión; si no existe, usa el operador de fusión nula (??) para devolver vacío y lo fuerza a string
    return (string) ($_SESSION['user']['nombre'] ?? '');
}

// Protege rutas restringidas obligando a que el visitante sea un administrador autenticado
function require_admin_auth(): void
{
    // Llama a la función de validación; si es positiva, detiene la ejecución de esta alerta y permite continuar al usuario
    if (is_admin_logged_in()) {
        return;
    }

    // Si no está validado, registra una alerta de tipo advertencia en la sesión
    set_flash('warning', 'Debes iniciar sesion para administrar productos.');
    // Desvía por la fuerza al usuario hacia la pantalla de login del panel de administración
    redirect('/tienda-barrio/admin/login.php');
}

// Procesa, valida y aloja una imagen subida mediante un formulario de productos en el servidor
function upload_product_image(string $fieldName = 'imagen_file'): ?string
{
    // Valida si la variable global $_FILES contiene datos bajo el nombre del campo del formulario y que sea un array estructurado
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        // Retorna nulo indicando que no se detectó ningún intento de subida
        return null;
    }

    // Asigna el bloque de información del archivo a una variable local para facilitar su lectura
    $file = $_FILES[$fieldName];
    // Extrae el código de error numérico de la subida; si no existe el índice, asume por defecto la constante de "sin archivo"
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    // Valida si el código de error corresponde explícitamente a que el usuario no seleccionó ningún archivo en el formulario
    if ($error === UPLOAD_ERR_NO_FILE) {
        // Retorna nulo sin generar fallos, ya que la imagen puede ser opcional
        return null;
    }

    // Valida si ocurrió algún tipo de problema técnico durante la transferencia del archivo (código diferente a OK)
    if ($error !== UPLOAD_ERR_OK) {
        // Interrumpe el flujo del programa lanzando una excepción en tiempo de ejecución con un mensaje descriptivo
        throw new RuntimeException('No se pudo cargar la imagen del producto.');
    }

    // Recupera la ruta temporal absoluta donde el servidor web guardó el archivo temporalmente
    $tmpPath = (string) ($file['tmp_name'] ?? '');
    // Analiza el contenido binario real del archivo temporal para identificar de manera segura su tipo MIME
    $mime = (string) mime_content_type($tmpPath);
    
    // Define una lista blanca de tipos MIME válidos asociados a sus respectivas extensiones de archivo permitidas
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
    ];

    // Verifica si el tipo MIME real detectado en el archivo temporal no se encuentra en el mapa de extensiones permitidas
    if (!isset($allowed[$mime])) {
        // Lanza una excepción bloqueante debido a que el archivo es un formato inválido o malicioso
        throw new RuntimeException('Formato no permitido. Usa JPG, PNG, WEBP o SVG.');
    }

    // Construye la ruta absoluta del sistema de archivos hacia la carpeta donde se almacenarán las imágenes de productos
    $uploadDir = __DIR__ . '/../img/productos/uploads';
    // Evalúa: si la carpeta no existe, intenta crearla de forma recursiva con permisos extendidos (0775); si falla, levanta un error
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        // Detiene el proceso notificando que los permisos del servidor o la ruta impiden la creación del directorio
        throw new RuntimeException('No fue posible crear la carpeta de imagenes.');
    }

    // Genera un nombre de archivo único uniendo un prefijo, la fecha actual, 8 caracteres aleatorios seguros en hexadecimal y su extensión final
    $fileName = 'prod_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    // Consolida la ruta de destino uniendo el directorio de subidas y el nuevo nombre único generado
    $targetPath = $uploadDir . '/' . $fileName;

    // Transfiere de forma segura el archivo desde su ubicación temporal hacia la ruta definitiva de almacenamiento del servidor
    if (!move_uploaded_file($tmpPath, $targetPath)) {
        // Si el servidor falla al mover el archivo (por espacio o permisos), rompe el flujo con una excepción
        throw new RuntimeException('No fue posible guardar la imagen cargada.');
    }

    // Devuelve la ruta web relativa/pública para que la base de datos la registre y las etiquetas HTML puedan renderizarla
    return '/tienda-barrio/img/productos/uploads/' . $fileName;
}

// Registra y simula el envío de una alerta automatizada por WhatsApp cuando un pedido cambia su estado logístico
function send_whatsapp_notification(int $pedidoId, string $estadoNuevo, string $estadoAnterior, string $telefono, string $clienteNombre): bool
{
    // Invoca e inicializa la conexión a la base de datos mediante PDO usando la función global db()
    $pdo = db();

    // Declara un diccionario para mapear los estados de la base de datos (claves) con texto legible para el cliente (valores)
    $estadosNombre = [
        'pendiente' => 'Pendiente',
        'preparando' => 'Preparando',
        'en_camino' => 'En camino',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    // Declara las estructuras de las frases comerciales personalizables según el nuevo estado en el que entre el pedido
    $mensajePlantilla = [
        'preparando' => "¡Hola {nombre}! Tu pedido está siendo preparado. Pronto te enviaremos más novedades.",
        'en_camino' => "¡Tu pedido está en camino! Llegaré pronto a tu domicilio.",
        'entregado' => "¡Tu pedido fue entregado! Gracias por tu compra en Mercadito La Esquina.",
        'cancelado' => "Tu pedido ha sido cancelado. Por favor contactanos para más información.",
    ];

    // Reemplaza la etiqueta de texto '{nombre}' por la variable del nombre real del cliente dentro de la plantilla seleccionada
    $mensaje = str_replace('{nombre}', $clienteNombre, $mensajePlantilla[$estadoNuevo] ?? '');

    try {
        // Prepara una sentencia SQL estructurada para insertar los detalles de la notificación de manera segura contra inyecciones SQL
        $insertNotif = $pdo->prepare(
            'INSERT INTO notificaciones (pedido_id, tipo, estado_anterior, estado_nuevo, telefono, mensaje, enviado) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        // Ejecuta la consulta SQL pasando los parámetros dinámicos requeridos en el mismo orden de los signos de interrogante
        $insertNotif->execute([$pedidoId, 'whatsapp', $estadoAnterior, $estadoNuevo, $telefono, $mensaje, false]);
        // Obtiene el identificador numérico (ID auto-incremental) asignado al nuevo registro insertado en la base de datos
        $notifId = (int) $pdo->lastInsertId();
        // [Área de desarrollo futuro]: Espacio reservado para integrar un SDK externo (Twilio, Baileys, etc.) que comunique con la API de WhatsApp
        // Se ejecuta una consulta de actualización para cambiar el estado de la notificación a verdadero (enviado = true) simulando éxito total
        $update = $pdo->prepare('UPDATE notificaciones SET enviado = ? WHERE id = ?');
        // Transmite los valores binarios (true) y el identificador de la fila para culminar el registro
        $update->execute([true, $notifId]);
        // Retorna verdadero confirmando que la lógica se procesó de punta a punta exitosamente
        return true;
    } catch (Throwable $e) {
        // En caso de que falle la base de datos, el query o falte una columna, atrapa la excepción y retorna falso para evitar la caída de la app
        return false;
    }
}


