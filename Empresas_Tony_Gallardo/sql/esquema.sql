-- Base de datos de la aplicación Empresas Tony Gallardo
-- Ejecutar como admin en phpMyAdmin o por consola mysql.

CREATE DATABASE IF NOT EXISTS empresas_tony
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Sustituir CONTRASENA_AQUI por la contraseña real (la misma de credenciales.php).
CREATE USER IF NOT EXISTS 'empresas_tony'@'localhost' IDENTIFIED BY 'CONTRASENA_AQUI';
GRANT ALL PRIVILEGES ON empresas_tony.* TO 'empresas_tony'@'localhost';
FLUSH PRIVILEGES;

USE empresas_tony;

CREATE TABLE IF NOT EXISTS familias (
  id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  orden  SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS empresas (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(160) NOT NULL,
  cif               VARCHAR(20)  DEFAULT NULL,
  sector            VARCHAR(120) DEFAULT NULL,
  familia_id        INT UNSIGNED DEFAULT NULL,
  ciclos            VARCHAR(255) DEFAULT NULL,
  direccion         VARCHAR(200) DEFAULT NULL,
  localidad         VARCHAR(100) DEFAULT NULL,
  provincia         VARCHAR(100) DEFAULT NULL,
  cp                VARCHAR(10)  DEFAULT NULL,
  telefono          VARCHAR(40)  DEFAULT NULL,
  email             VARCHAR(160) DEFAULT NULL,
  web               VARCHAR(200) DEFAULT NULL,
  contacto_nombre   VARCHAR(160) DEFAULT NULL,
  contacto_cargo    VARCHAR(120) DEFAULT NULL,
  contacto_telefono VARCHAR(40)  DEFAULT NULL,
  contacto_email    VARCHAR(160) DEFAULT NULL,
  plazas            SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  convenio          TINYINT(1)   NOT NULL DEFAULT 0,
  estado            VARCHAR(20)  NOT NULL DEFAULT 'Activa',
  observaciones     TEXT         DEFAULT NULL,
  creado_en         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_nombre (nombre),
  KEY idx_estado (estado),
  CONSTRAINT fk_empresa_familia FOREIGN KEY (familia_id)
    REFERENCES familias (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO familias (nombre, orden) VALUES
  ('Administración y Gestión', 10),
  ('Actividades Físicas y Deportivas', 20),
  ('Comercio y Marketing', 30),
  ('Electricidad y Electrónica', 40),
  ('Fabricación Mecánica', 50),
  ('Hostelería y Turismo', 60),
  ('Imagen Personal', 70),
  ('Imagen y Sonido', 80),
  ('Informática y Comunicaciones', 90),
  ('Instalación y Mantenimiento', 100),
  ('Sanidad', 110),
  ('Servicios Socioculturales y a la Comunidad', 120),
  ('Transporte y Mantenimiento de Vehículos', 130);
