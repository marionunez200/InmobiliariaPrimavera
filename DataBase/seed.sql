-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: inmobiliaria_db
-- ------------------------------------------------------
-- Server version	9.7.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '0f341035-6eb4-11f1-af9d-028855667746:1-437';

--
-- Table structure for table `agentes`
--

DROP TABLE IF EXISTS `agentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agentes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias_propiedad`
--

DROP TABLE IF EXISTS `categorias_propiedad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_propiedad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `protegida` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `imagenes_propiedades`
--

DROP TABLE IF EXISTS `imagenes_propiedades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imagenes_propiedades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `propiedad_id` int NOT NULL,
  `imagen_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto_alternativo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_principal` tinyint DEFAULT '0',
  `orden` int DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_imagenes_propiedad` (`propiedad_id`),
  CONSTRAINT `fk_imagenes_propiedades` FOREIGN KEY (`propiedad_id`) REFERENCES `propiedades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `intentos_login`
--

DROP TABLE IF EXISTS `intentos_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intentos_login` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intentos` int NOT NULL DEFAULT '0',
  `ultimo_intento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuario_ip` (`usuario`,`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mensajes_contacto`
--

DROP TABLE IF EXISTS `mensajes_contacto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensajes_contacto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `propiedad_id` int DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_mensaje` enum('nuevo','leido','contactado','cerrado') COLLATE utf8mb4_unicode_ci DEFAULT 'nuevo',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mensajes_propiedad` (`propiedad_id`),
  CONSTRAINT `fk_mensajes_propiedades` FOREIGN KEY (`propiedad_id`) REFERENCES `propiedades` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiracion` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `fk_password_reset_usuario` (`usuario_id`),
  CONSTRAINT `fk_password_reset_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `propiedades`
--

DROP TABLE IF EXISTS `propiedades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `propiedades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agente_id` int NOT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio` decimal(12,2) NOT NULL,
  `moneda` char(3) COLLATE utf8mb4_unicode_ci DEFAULT 'MXN',
  `tipo_operacion` enum('venta','renta','traspaso') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_publicacion` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `destacada` tinyint DEFAULT '0',
  `ciudad` enum('navojoa','san_carlos','ciudad_obregon','guaymas') COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion_completa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_maps_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recamaras` tinyint unsigned DEFAULT '0',
  `banos` decimal(3,1) DEFAULT '0.0',
  `estacionamientos` tinyint unsigned DEFAULT '0',
  `terreno_m2` decimal(10,2) DEFAULT NULL,
  `construccion_m2` decimal(10,2) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `categoria_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_propiedades_agente` (`agente_id`),
  KEY `idx_propiedades_filtros` (`estado_publicacion`,`ciudad`,`tipo_operacion`,`precio`),
  KEY `idx_propiedades_destacadas` (`destacada`),
  CONSTRAINT `fk_propiedades_agentes` FOREIGN KEY (`agente_id`) REFERENCES `agentes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios_admin`
--

DROP TABLE IF EXISTS `usuarios_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('admin','editor') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `activo` tinyint DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-23 17:23:53
