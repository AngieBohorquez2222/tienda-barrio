-- ============================================================
-- PROYECTO: Tienda de Barrio
-- OBJETIVO: Script didáctico para estudiantes (MySQL)
-- ============================================================

-- 1️⃣ Crear la base de datos (solo si no existe)
CREATE DATABASE IF NOT EXISTS tienda_barrio
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2️⃣ Activar el uso de la base de datos
USE tienda_barrio;

-- ============================================================
-- TABLA: productos
-- Guarda los productos disponibles en la tienda.
-- ============================================================
CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,               -- Identificador único
  nombre VARCHAR(120) NOT NULL,                    -- Nombre del producto
  imagen VARCHAR(255) NOT NULL DEFAULT '/tienda-de-barrio/img/productos/default.svg', -- Imagen del producto
  precio DECIMAL(10,2) NOT NULL CHECK (precio >= 0), -- Precio (no puede ser negativo)
  stock INT(11) NOT NULL CHECK (stock >= 0),       -- Cantidad disponible
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Fecha de actualización
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: pedidos
-- Registra los pedidos realizados por los clientes.
-- ============================================================
CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,               -- Identificador único
  codigo VARCHAR(20) NOT NULL UNIQUE,              -- Código único del pedido
  cliente_nombre VARCHAR(120) NOT NULL,            -- Nombre del cliente
  cliente_telefono VARCHAR(30) NOT NULL,           -- Teléfono del cliente
  cliente_direccion VARCHAR(180) NOT NULL,         -- Dirección de entrega
  total DECIMAL(10,2) NOT NULL,                    -- Total del pedido
  estado ENUM('pendiente','preparando','en_camino','entregado','cancelado') NOT NULL DEFAULT 'pendiente', -- Estado del pedido
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Fecha de actualización
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: pedido_items
-- Guarda los productos incluidos en cada pedido.
-- Relación: pedido_items.pedido_id → pedidos.id
-- ============================================================
CREATE TABLE pedido_items (
  id INT AUTO_INCREMENT PRIMARY KEY,               -- Identificador único
  pedido_id INT NOT NULL,                          -- Relación con el pedido (FK)
  producto_id INT NOT NULL,                        -- Relación con el producto (FK)
  nombre_producto VARCHAR(120) NOT NULL,           -- Nombre del producto
  precio_unitario DECIMAL(10,2) NOT NULL,          -- Precio unitario
  cantidad INT NOT NULL,                           -- Cantidad comprada
  subtotal DECIMAL(10,2) NOT NULL,                 -- Total parcial (precio * cantidad)
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
  CONSTRAINT fk_pedido_items_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_pedido_items_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: notificaciones
-- Guarda los mensajes enviados al cliente sobre el estado del pedido.
-- Relación: notificaciones.pedido_id → pedidos.id
-- ============================================================
CREATE TABLE notificaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,               -- Identificador único
  pedido_id INT NOT NULL,                          -- Relación con el pedido (FK)
  tipo VARCHAR(30) NOT NULL,                       -- Tipo de notificación (ejemplo: whatsapp)
  estado_anterior VARCHAR(20) DEFAULT NULL,        -- Estado previo del pedido
  estado_nuevo VARCHAR(20) DEFAULT NULL,           -- Estado nuevo del pedido
  telefono VARCHAR(30) NOT NULL,                   -- Teléfono del cliente
  mensaje TEXT NOT NULL,                           -- Texto del mensaje enviado
  enviado TINYINT(1) DEFAULT 0,                    -- Indica si fue enviado (1 = sí)
  respuesta TEXT DEFAULT NULL,                     -- Respuesta del cliente (si aplica)
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
  CONSTRAINT fk_notificaciones_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: usuarios_admin
-- Guarda los datos de los administradores del sistema.
-- ============================================================
CREATE TABLE usuarios_admin (
  id INT AUTO_INCREMENT PRIMARY KEY,               -- Identificador único
  usuario VARCHAR(60) NOT NULL UNIQUE,             -- Nombre de usuario (único)
  nombre VARCHAR(120) NOT NULL,                    -- Nombre completo del administrador
  password VARCHAR(255) NOT NULL,                  -- Contraseña encriptada
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha de creación
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: usuarios
-- Guarda los datos de los clientes/usuarios registrados.
-- ============================================================
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,               -- Identificador único
  usuario VARCHAR(60) NOT NULL UNIQUE,             -- Nombre de usuario (único)
  nombre VARCHAR(120) NOT NULL,                    -- Nombre completo
  email VARCHAR(120) NOT NULL UNIQUE,              -- Email del usuario
  telefono VARCHAR(30) NOT NULL,                   -- Teléfono del usuario
  direccion VARCHAR(180) NOT NULL,                 -- Dirección del usuario
  password VARCHAR(255) NOT NULL,                  -- Contraseña encriptada
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha de creación
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CONSULTAS DE PRUEBA (opcionales para clase)
-- ============================================================

-- Ver todos los pedidos con sus productos
-- SELECT p.codigo, i.nombre_producto, i.cantidad, i.subtotal
-- FROM pedidos p
-- INNER JOIN pedido_items i ON p.id = i.pedido_id;

-- Ver notificaciones enviadas a un cliente
-- SELECT n.mensaje, n.estado_nuevo, p.cliente_nombre
-- FROM notificaciones n
-- INNER JOIN pedidos p ON n.pedido_id = p.id;

-- Ver productos con stock menor a 10
-- SELECT nombre, stock FROM productos WHERE stock < 10;
