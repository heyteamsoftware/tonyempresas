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
  nombre_comercial  VARCHAR(160) DEFAULT NULL,
  cif               VARCHAR(20)  DEFAULT NULL,
  sector            VARCHAR(120) DEFAULT NULL,
  familia_id        INT UNSIGNED DEFAULT NULL,
  tamanio           VARCHAR(40)  DEFAULT NULL,
  tipo_empresa      VARCHAR(255) DEFAULT NULL,
  instalaciones     VARCHAR(255) DEFAULT NULL,
  colaborado_antes  TINYINT(1)   NOT NULL DEFAULT 0,
  tipo_colaboracion VARCHAR(160) DEFAULT NULL,
  ciclos            VARCHAR(255) DEFAULT NULL,
  actividades_formativas VARCHAR(255) DEFAULT NULL,
  prl_evaluacion    TINYINT(1)   NOT NULL DEFAULT 0,
  prl_formacion     TINYINT(1)   NOT NULL DEFAULT 0,
  prl_epis          TINYINT(1)   NOT NULL DEFAULT 0,
  horario           VARCHAR(160) DEFAULT NULL,
  jornada           VARCHAR(80)  DEFAULT NULL,
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
  origen            VARCHAR(20)  NOT NULL DEFAULT 'interno',
  observaciones     TEXT         DEFAULT NULL,
  creado_en         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  eliminada_en      DATETIME     DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_nombre (nombre),
  KEY idx_estado (estado),
  CONSTRAINT fk_empresa_familia FOREIGN KEY (familia_id)
    REFERENCES familias (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Una empresa puede pertenecer a varias familias profesionales.
-- empresas.familia_id se mantiene como "familia principal" (la primera del catálogo).
CREATE TABLE IF NOT EXISTS empresa_familias (
  empresa_id INT UNSIGNED NOT NULL,
  familia_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (empresa_id, familia_id),
  KEY idx_familia (familia_id),
  CONSTRAINT fk_ef_empresa FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ef_familia FOREIGN KEY (familia_id) REFERENCES familias (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personas distintas que han entrado (identificador aleatorio en cookie, sin IP).
CREATE TABLE IF NOT EXISTS visitantes (
  id        CHAR(32) NOT NULL PRIMARY KEY,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ajustes (
  clave VARCHAR(50)  NOT NULL PRIMARY KEY,
  valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO familias (nombre, orden) VALUES
  ('Servicios Socioculturales y a la Comunidad', 10),
  ('Seguridad y Medio Ambiente', 20),
  ('Madera, Mueble y Corcho', 30),
  ('Actividades Físicas y Deportivas', 40),
  ('Instalación y Mantenimiento', 50),
  ('Sanidad', 60),
  ('Industrias Alimentarias', 70),
  ('Comercio y Marketing', 80),
  ('Hostelería y Turismo', 90);
