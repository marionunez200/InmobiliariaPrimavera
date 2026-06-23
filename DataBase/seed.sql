USE inmobiliaria_db;

INSERT INTO usuarios_admin (
    nombre,
    email,
    password_hash,
    rol
) VALUES (
    'Administrador',
    'admin@inmobiliaria.com',
    '$2y$10$ejemploTemporalCambiaEstoDespues',
    'admin'
);


INSERT INTO agentes (
    nombre,
    telefono,
    email,
    foto_url
) VALUES
(
    'Carlos Ramírez',
    '6441234567',
    'carlos@inmobiliaria.com',
    'uploads/agentes/carlos.jpg'
),
(
    'Ana López',
    '6449876543',
    'ana@inmobiliaria.com',
    'uploads/agentes/ana.jpg'
);


INSERT INTO propiedades (
    agente_id,
    titulo,
    slug,
    descripcion,
    precio,
    tipo_operacion,
    tipo_propiedad,
    estado_publicacion,
    destacada,
    ciudad,
    direccion_completa,
    google_maps_url,
    recamaras,
    banos,
    estacionamientos,
    terreno_m2,
    construccion_m2
) VALUES
(
    1,
    'Casa moderna en Ciudad Obregón',
    'casa-moderna-ciudad-obregon',
    'Casa amplia con cocina integral, patio trasero, cochera para dos autos y excelente ubicación.',
    2500000.00,
    'venta',
    'casa',
    'activo',
    1,
    'ciudad_obregon',
    'Monte oscuro #3212, Colonia Bella Vista, Ciudad Obregón, Sonora.',
    NULL,
    3,
    2.5,
    2,
    250.00,
    180.00
),
(
    2,
    'Terreno en Navojoa',
    'terreno-en-navojoa',
    'Terreno amplio ideal para construcción residencial, ubicado en zona tranquila.',
    850000.00,
    'venta',
    'terreno',
    'activo',
    0,
    'navojoa',
    'Calle Principal #456, Navojoa, Sonora.',
    NULL,
    0,
    0,
    0,
    400.00,
    0.00
),
(
    1,
    'Local comercial en Guaymas',
    'local-comercial-guaymas',
    'Local comercial con buena ubicación, ideal para oficina, consultorio o negocio pequeño.',
    12000.00,
    'renta',
    'local_comercial',
    'activo',
    0,
    'guaymas',
    'Av. Serdán #210, Guaymas, Sonora.',
    NULL,
    0,
    1,
    1,
    80.00,
    80.00
),
(
    2,
    'Departamento en San Carlos',
    'departamento-san-carlos',
    'Departamento cómodo cerca de zona turística, ideal para descanso o renta vacacional.',
    7500.00,
    'renta',
    'departamento',
    'activo',
    0,
    'san_carlos',
    'Boulevard Manlio Fabio Beltrones #850, San Carlos, Sonora.',
    NULL,
    2,
    1,
    1,
    0.00,
    70.00
);


INSERT INTO imagenes_propiedades (
    propiedad_id,
    imagen_url,
    texto_alternativo,
    es_principal,
    orden
) VALUES
(
    1,
    'uploads/propiedades/casa-obregon-1.jpg',
    'Fachada de casa moderna en Ciudad Obregón',
    1,
    1
),
(
    1,
    'uploads/propiedades/casa-obregon-2.jpg',
    'Interior de casa moderna en Ciudad Obregón',
    0,
    2
),
(
    2,
    'uploads/propiedades/terreno-navojoa-1.jpg',
    'Terreno en Navojoa',
    1,
    1
),
(
    3,
    'uploads/propiedades/local-guaymas-1.jpg',
    'Local comercial en Guaymas',
    1,
    1
),
(
    4,
    'uploads/propiedades/departamento-san-carlos-1.jpg',
    'Departamento en San Carlos',
    1,
    1
);