CREATE DATABASE inmobiliaria_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE inmobiliaria_db;

CREATE TABLE usuarios_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,

    rol ENUM('admin', 'editor') DEFAULT 'admin',
    activo TINYINT DEFAULT 1,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE agentes (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(30),
    email VARCHAR(150),
    foto_url VARCHAR(255),

    activo TINYINT DEFAULT 1,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE propiedades (
    id INT AUTO_INCREMENT PRIMARY KEY,

    agente_id INT NOT NULL,

    titulo VARCHAR(150) NOT NULL,
    slug VARCHAR(180) UNIQUE,

    descripcion TEXT,

    precio DECIMAL(12,2) NOT NULL,
    moneda CHAR(3) DEFAULT 'MXN',

    tipo_operacion ENUM('venta', 'renta', 'traspaso') NOT NULL,

    tipo_propiedad ENUM(
        'casa',
        'departamento',
        'local_comercial',
        'terreno'
    ) NOT NULL,

    estado_publicacion ENUM('activo', 'inactivo') DEFAULT 'activo',

    destacada TINYINT DEFAULT 0,

    ciudad ENUM(
        'navojoa',
        'san_carlos',
        'ciudad_obregon',
        'guaymas'
    ) NOT NULL,

    direccion_completa VARCHAR(255) NOT NULL,
    google_maps_url VARCHAR(500),

    recamaras TINYINT UNSIGNED DEFAULT 0,
    banos INT DEFAULT 0,
    estacionamientos TINYINT UNSIGNED DEFAULT 0,

    terreno_m2 DECIMAL(10,2),
    construccion_m2 DECIMAL(10,2),

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_propiedades_agentes
        FOREIGN KEY (agente_id)
        REFERENCES agentes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE imagenes_propiedades (
    id INT AUTO_INCREMENT PRIMARY KEY,

    propiedad_id INT NOT NULL,

    imagen_url VARCHAR(255) NOT NULL,
    texto_alternativo VARCHAR(150),
    es_principal TINYINT DEFAULT 0,
    orden INT DEFAULT 0,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_imagenes_propiedades
        FOREIGN KEY (propiedad_id)
        REFERENCES propiedades(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE mensajes_contacto (
    id INT AUTO_INCREMENT PRIMARY KEY,

    propiedad_id INT NULL,

    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(30),
    email VARCHAR(150),
    mensaje TEXT NOT NULL,

    estado_mensaje ENUM('nuevo', 'leido', 'contactado', 'cerrado') DEFAULT 'nuevo',

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completado_en DATETIME NULL,

    CONSTRAINT fk_mensajes_propiedades
        FOREIGN KEY (propiedad_id)
        REFERENCES propiedades(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_propiedades_agente
ON propiedades(agente_id);

CREATE INDEX idx_propiedades_filtros
ON propiedades(
    estado_publicacion,
    ciudad,
    tipo_operacion,
    tipo_propiedad,
    precio
);

CREATE INDEX idx_propiedades_destacadas
ON propiedades(destacada);

CREATE INDEX idx_imagenes_propiedad
ON imagenes_propiedades(propiedad_id);

CREATE INDEX idx_mensajes_propiedad
ON mensajes_contacto(propiedad_id);