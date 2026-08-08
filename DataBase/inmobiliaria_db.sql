-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-08-2026 a las 02:45:10
-- Versión del servidor: 9.7.1
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inmobiliaria_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agentes`
--

CREATE TABLE `agentes` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `agentes`
--

INSERT INTO `agentes` (`id`, `usuario_id`, `nombre`, `telefono`, `email`, `foto_url`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 18, 'Sandra Castillo', '6441345244', 'primaverainmobiliarias.c@gmail.com', 'Imagenes/agente1.webp', 1, '2026-08-07 01:09:15', '2026-08-07 01:09:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_propiedad`
--

CREATE TABLE `categorias_propiedad` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `protegida` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias_propiedad`
--

INSERT INTO `categorias_propiedad` (`id`, `nombre`, `activo`, `protegida`) VALUES
(1, 'Almacen', 1, 1),
(2, 'Casa', 1, 1),
(3, 'Departamento', 1, 1),
(4, 'Terreno', 1, 1),
(5, 'Local comercial', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE `ciudades` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `protegida` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `ciudades`
--

INSERT INTO `ciudades` (`id`, `nombre`, `activo`, `protegida`) VALUES
(1, 'Ciudad Obregón', 1, 1),
(2, 'Navojoa', 1, 1),
(3, 'San Carlos', 1, 1),
(4, 'Guaymas', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_propiedades`
--

CREATE TABLE `imagenes_propiedades` (
  `id` int NOT NULL,
  `propiedad_id` int NOT NULL,
  `imagen_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto_alternativo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_principal` tinyint DEFAULT '0',
  `orden` int DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `imagenes_propiedades`
--

INSERT INTO `imagenes_propiedades` (`id`, `propiedad_id`, `imagen_url`, `texto_alternativo`, `es_principal`, `orden`, `creado_en`) VALUES
(1, 1, 'Uploads/propiedades/propiedad-1-6a753246eac7e.webp', 'Casa en Punta Arena, Guaymas', 0, 1, '2026-08-07 01:17:59'),
(2, 1, 'Uploads/propiedades/propiedad-1-6a7532470a256.webp', 'Casa en Punta Arena, Guaymas', 0, 2, '2026-08-07 01:17:59'),
(3, 1, 'Uploads/propiedades/propiedad-1-6a7532471cdac.webp', 'Casa en Punta Arena, Guaymas', 1, 3, '2026-08-07 01:17:59'),
(4, 3, 'Uploads/propiedades/propiedad-3-6a753764c62d9.webp', 'Casa de esperanza', 0, 1, '2026-08-07 01:39:48'),
(5, 3, 'Uploads/propiedades/propiedad-3-6a753764de4ef.webp', 'Casa de esperanza', 0, 2, '2026-08-07 01:39:49'),
(6, 3, 'Uploads/propiedades/propiedad-3-6a75376548b14.webp', 'Casa de esperanza', 0, 3, '2026-08-07 01:39:49'),
(7, 3, 'Uploads/propiedades/propiedad-3-6a753765a699f.webp', 'Casa de esperanza', 0, 4, '2026-08-07 01:39:50'),
(9, 3, 'Uploads/propiedades/propiedad-3-6a7537666b6b1.webp', 'Casa de esperanza', 0, 6, '2026-08-07 01:39:50'),
(10, 3, 'Uploads/propiedades/propiedad-3-6a753766afa7c.webp', 'Casa de esperanza', 0, 7, '2026-08-07 01:39:51'),
(11, 3, 'Uploads/propiedades/propiedad-3-6a7537671c62f.webp', 'Casa de esperanza', 0, 8, '2026-08-07 01:39:51'),
(12, 3, 'Uploads/propiedades/propiedad-3-6a7537677a5d0.webp', 'Casa de esperanza', 0, 9, '2026-08-07 01:39:51'),
(13, 3, 'Uploads/propiedades/propiedad-3-6a753767d718f.webp', 'Casa de esperanza', 0, 10, '2026-08-07 01:39:52'),
(14, 3, 'Uploads/propiedades/propiedad-3-6a7537680071d.webp', 'Casa de esperanza', 0, 11, '2026-08-07 01:39:52'),
(15, 3, 'Uploads/propiedades/propiedad-3-6a75376840126.webp', 'Casa de esperanza', 0, 12, '2026-08-07 01:39:52'),
(16, 3, 'Uploads/propiedades/propiedad-3-6a75376885317.webp', 'Casa de esperanza', 0, 13, '2026-08-07 01:39:52'),
(17, 3, 'Uploads/propiedades/propiedad-3-6a753768a280a.webp', 'Casa de esperanza', 0, 14, '2026-08-07 01:39:52'),
(18, 3, 'Uploads/propiedades/propiedad-3-6a753768bd032.webp', 'Casa de esperanza', 0, 15, '2026-08-07 01:39:52'),
(19, 3, 'Uploads/propiedades/propiedad-3-6a753768f388d.webp', 'Casa de esperanza', 0, 16, '2026-08-07 01:39:53'),
(21, 3, 'Uploads/propiedades/propiedad-3-6a75376961c0d.webp', 'Casa de esperanza', 0, 18, '2026-08-07 01:39:53'),
(22, 3, 'Uploads/propiedades/propiedad-3-6a75376997f24.webp', 'Casa de esperanza', 0, 19, '2026-08-07 01:39:53'),
(23, 3, 'Uploads/propiedades/propiedad-3-6a753769d6808.webp', 'Casa de esperanza', 0, 20, '2026-08-07 01:39:53'),
(24, 3, 'Uploads/propiedades/propiedad-3-6a7537ae25c44.webp', 'Casa de esperanza', 1, 21, '2026-08-07 01:41:02'),
(25, 2, 'Uploads/propiedades/propiedad-2-6a7537eecd390.webp', 'Fraccionamiento San Gabriel', 0, 1, '2026-08-07 01:42:07'),
(26, 2, 'Uploads/propiedades/propiedad-2-6a7537ef0407a.webp', 'Fraccionamiento San Gabriel', 0, 2, '2026-08-07 01:42:07'),
(27, 2, 'Uploads/propiedades/propiedad-2-6a7537ef2c579.webp', 'Fraccionamiento San Gabriel', 0, 3, '2026-08-07 01:42:07'),
(28, 2, 'Uploads/propiedades/propiedad-2-6a7537ef52260.webp', 'Fraccionamiento San Gabriel', 0, 4, '2026-08-07 01:42:07'),
(29, 2, 'Uploads/propiedades/propiedad-2-6a7537ef7ab98.webp', 'Fraccionamiento San Gabriel', 0, 5, '2026-08-07 01:42:07'),
(30, 2, 'Uploads/propiedades/propiedad-2-6a7537efa0df8.webp', 'Fraccionamiento San Gabriel', 0, 6, '2026-08-07 01:42:07'),
(31, 2, 'Uploads/propiedades/propiedad-2-6a7537efccbf0.webp', 'Fraccionamiento San Gabriel', 1, 7, '2026-08-07 01:42:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_login`
--

CREATE TABLE `intentos_login` (
  `id` int NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `intentos` int NOT NULL DEFAULT '0',
  `ultimo_intento` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_contacto`
--

CREATE TABLE `mensajes_contacto` (
  `id` int NOT NULL,
  `propiedad_id` int DEFAULT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_mensaje` enum('nuevo','leido','contactado','cerrado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'nuevo',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completado_en` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `operaciones_realizadas`
--

CREATE TABLE `operaciones_realizadas` (
  `id` int NOT NULL,
  `propiedad_id` int NOT NULL,
  `agente_id` int NOT NULL,
  `tipo_operacion` enum('venta','renta','traspaso') NOT NULL,
  `cliente_nombre` varchar(150) NOT NULL,
  `cliente_telefono` varchar(30) DEFAULT NULL,
  `cliente_email` varchar(150) DEFAULT NULL,
  `fecha_operacion` date NOT NULL,
  `precio` decimal(12,2) NOT NULL,
  `moneda` char(3) DEFAULT 'MXN',
  `meses_renta` int DEFAULT NULL,
  `comision` decimal(12,2) DEFAULT '0.00',
  `forma_pago` enum('contado','credito','financiamiento') DEFAULT 'contado',
  `numero_contrato` varchar(50) DEFAULT NULL,
  `estado` enum('vigente','cancelada') DEFAULT 'vigente',
  `observaciones` text,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracion` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades`
--

CREATE TABLE `propiedades` (
  `id` int NOT NULL,
  `agente_id` int NOT NULL,
  `titulo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `precio` decimal(12,2) NOT NULL,
  `moneda` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'MXN',
  `tipo_operacion` enum('venta','renta','traspaso') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_publicacion` enum('activo','inactivo','vendido','rentado','traspasado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `destacada` tinyint DEFAULT '0',
  `direccion_completa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_maps_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recamaras` tinyint UNSIGNED DEFAULT '0',
  `banos` int DEFAULT '0',
  `estacionamientos` tinyint UNSIGNED DEFAULT '0',
  `terreno_m2` decimal(10,2) DEFAULT NULL,
  `construccion_m2` decimal(10,2) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `categoria_id` int NOT NULL,
  `ciudad_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `propiedades`
--

INSERT INTO `propiedades` (`id`, `agente_id`, `titulo`, `slug`, `descripcion`, `precio`, `moneda`, `tipo_operacion`, `estado_publicacion`, `destacada`, `direccion_completa`, `google_maps_url`, `recamaras`, `banos`, `estacionamientos`, `terreno_m2`, `construccion_m2`, `creado_en`, `actualizado_en`, `categoria_id`, `ciudad_id`) VALUES
(1, 1, 'Casa en Punta Arena, Guaymas', 'casa-en-punta-arena-guaymas-6e92ab', 'Vista privilegiada, espacios abiertos y modernos. Especial para una inversión en zona de crecimiento.', 17000000.00, 'MXN', 'venta', 'activo', 0, 'Colonia punta arena', 'https://www.google.com/maps?q=Colonia%20punta%20arena&output=embed', 3, 2, 1, 250.00, 232.00, '2026-08-07 01:17:58', '2026-08-07 23:19:57', 2, 4),
(2, 1, 'Fraccionamiento San Gabriel', 'fraccionamiento-san-gabriel-77fbdb', 'Cuenta con patio amplio, puerta de herrería y parque enfrente', 860000.00, 'MXN', 'venta', 'activo', 0, 'calle hidra #1229 fraccionamiento San Gabriel', 'https://www.google.com/maps?q=calle%20hidra%20%231229%20fraccionamiento%20San%20Gabriel&output=embed', 2, 1, 1, 128.70, 52.90, '2026-08-07 01:26:31', '2026-08-07 23:19:57', 2, 1),
(3, 1, 'Casa de esperanza', 'casa-de-esperanza-4c5b7e', '', 1999900.00, 'MXN', 'venta', 'activo', 0, 'm', 'https://www.google.com/maps?q=m&output=embed', 5, 4, 2, 257.40, 20.35, '2026-08-07 01:39:48', '2026-08-07 23:19:57', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_admin`
--

CREATE TABLE `usuarios_admin` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('admin','editor') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `activo` tinyint DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `two_factor_secret` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_admin`
--

INSERT INTO `usuarios_admin` (`id`, `nombre`, `email`, `password_hash`, `rol`, `activo`, `creado_en`, `actualizado_en`, `two_factor_secret`, `two_factor_enabled`) VALUES
(1, 'Administrador', 'adminPrimavera@gmail.com', '$2y$10$UFu00dXFDrFqZ6Zn6pvtv.Dhl1HwddigMScF/inTyOIZWMZG.iMqO', 'admin', 1, '2026-06-23 06:08:25', '2026-08-03 01:38:10', 'OQYPVGUZUAUIJALC', 1),
(2, 'Administrador2', 'adminPrimavera2@gmail.com', '$2y$10$jqKaBZFtUzMWRx.SVFhOXugTfgDXpc2o9nD2/DBVUNxzaCs4hMkzq', 'admin', 1, '2026-08-07 02:41:20', '2026-08-07 02:51:19', 'HRXHQTWKLBDQ5MKK', 1),
(18, 'Sandra Castillo', 'primaverainmobiliarias.c@gmail.com', '$2b$10$R9h/cIPJ0GI.URNN63.u2e4dJqT1zQ8.m0/J3kY9Q4Y8P1Z6W2X3Y', 'admin', 1, '2026-08-07 01:09:15', '2026-08-07 02:45:49', NULL, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agentes`
--
ALTER TABLE `agentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agentes_usuario` (`usuario_id`);

--
-- Indices de la tabla `categorias_propiedad`
--
ALTER TABLE `categorias_propiedad`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `imagenes_propiedades`
--
ALTER TABLE `imagenes_propiedades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_imagenes_propiedad` (`propiedad_id`);

--
-- Indices de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`,`ip`);

--
-- Indices de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mensajes_propiedad` (`propiedad_id`);

--
-- Indices de la tabla `operaciones_realizadas`
--
ALTER TABLE `operaciones_realizadas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_operacion_agente` (`agente_id`),
  ADD KEY `fk_operacion_propiedad` (`propiedad_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_password_reset_usuario` (`usuario_id`);

--
-- Indices de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_propiedades_agente` (`agente_id`),
  ADD KEY `idx_propiedades_filtros` (`estado_publicacion`,`tipo_operacion`,`precio`),
  ADD KEY `idx_propiedades_destacadas` (`destacada`),
  ADD KEY `fk_propiedades_ciudades` (`ciudad_id`);

--
-- Indices de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agentes`
--
ALTER TABLE `agentes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias_propiedad`
--
ALTER TABLE `categorias_propiedad`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `imagenes_propiedades`
--
ALTER TABLE `imagenes_propiedades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `operaciones_realizadas`
--
ALTER TABLE `operaciones_realizadas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agentes`
--
ALTER TABLE `agentes`
  ADD CONSTRAINT `fk_agente_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agentes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `imagenes_propiedades`
--
ALTER TABLE `imagenes_propiedades`
  ADD CONSTRAINT `fk_imagenes_propiedades` FOREIGN KEY (`propiedad_id`) REFERENCES `propiedades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD CONSTRAINT `fk_mensajes_propiedades` FOREIGN KEY (`propiedad_id`) REFERENCES `propiedades` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `operaciones_realizadas`
--
ALTER TABLE `operaciones_realizadas`
  ADD CONSTRAINT `fk_operacion_agente` FOREIGN KEY (`agente_id`) REFERENCES `agentes` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_operacion_propiedad` FOREIGN KEY (`propiedad_id`) REFERENCES `propiedades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_reset_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD CONSTRAINT `fk_propiedades_agentes` FOREIGN KEY (`agente_id`) REFERENCES `agentes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_propiedades_ciudades` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
