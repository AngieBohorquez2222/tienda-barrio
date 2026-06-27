USE tienda_barrio;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(60) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  telefono VARCHAR(30) NOT NULL,
  direccion VARCHAR(180) NOT NULL,
  password VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario de ejemplo (contraseña: cliente123)
-- Para crear el hash correcto, ejecutar: INSERT INTO usuarios (usuario, nombre, email, telefono, direccion, password) VALUES
-- ('cliente', 'Cliente Demo', 'cliente@tienda.com', '3001234567', 'Calle 1 # 2-3', '<hash_de_password>');