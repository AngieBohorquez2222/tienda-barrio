<?php
// Activa el modo estricto de tipos para mejor seguridad
declare(strict_types=1);

// Inicia la sesión si no está activa para usar variables de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos - servidor MySQL
const DB_HOST = 'localhost';
// Nombre de la base de datos
const DB_NAME = 'tienda_barrio';
// Usuario de MySQL (root por defecto en XAMPP)
const DB_USER = 'root';
// Contraseña vacía por defecto en XAMPP
const DB_PASS = '';
// Nombre de la tienda para mostrar en la página
const STORE_NAME = 'Mercadito La Esquina de Ciudad Bolivar';
// Teléfono de la tienda para contacto
const STORE_PHONE = '6010000000';
// WhatsApp de la tienda para notificaciones
const STORE_WHATSAPP = '573001112233';

// Función que devuelve la conexión PDO a la base de datos (singleton)
function db(): PDO
{
    // Variable estática para mantener la conexión única
    static $pdo = null;

    // Si no hay conexión, crear una nueva
    if ($pdo === null) {
        // DSN de conexión MySQL con charset utf8mb4
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        // Crear conexión PDO con configuración de errores y fetch mode
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Lanza excepciones en errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch como array asociativo
        ]);
    }

    // Retornar la conexión
    return $pdo;
}