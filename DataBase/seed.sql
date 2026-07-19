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

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '0f341035-6eb4-11f1-af9d-028855667746:1-170,
3099865d-6da0-11f1-aed4-00e04cb20f2c:1-177';

--
-- Dumping data for table `agentes`
--

LOCK TABLES `agentes` WRITE;
/*!40000 ALTER TABLE `agentes` DISABLE KEYS */;
INSERT INTO `agentes` VALUES (2,'Ana Lopez','6449876543','ana@inmobiliaria.com','Uploads/agentes/agente-6a4f1cffc17f5.png',0,'2026-06-23 06:08:25','2026-07-09 04:01:03'),(3,'Maria Fernandez','644 567 8901','uncorreo@gmail.com','Uploads/agentes/agente-6a4f1cf86a7c5.jpg',0,'2026-06-23 18:47:25','2026-07-09 04:00:56'),(4,'Mario Rodriguez','644 567 8901','uncorreo@gmail.com','Uploads/agentes/agente-6a4f1d0746ee1.png',0,'2026-06-23 18:48:08','2026-07-09 04:01:11'),(6,'Mario PPerez','644 567 8901','uncorreo@gmail.com','Uploads/agentes/agente-6a4ea91bba0e1.png',1,'2026-06-23 18:51:00','2026-07-08 19:50:42'),(7,'Mario PPerez','644 567 8901','uncorreo@gmail.com','Uploads/agentes/agente-6a4cb07feaa58.png',1,'2026-06-23 22:22:14','2026-07-13 08:35:47'),(8,'adsdw','6441234567','marioZpro026@gmail.com','Uploads/agentes/agente-6a4cb187b3a73.jpg',1,'2026-07-07 07:29:34','2026-07-07 07:57:59');
/*!40000 ALTER TABLE `agentes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `categorias_propiedad`
--

LOCK TABLES `categorias_propiedad` WRITE;
/*!40000 ALTER TABLE `categorias_propiedad` DISABLE KEYS */;
INSERT INTO `categorias_propiedad` VALUES (1,'Almacen',1,1),(2,'Casa',1,1),(3,'Departamento',1,1),(4,'Terreno',1,1),(5,'Local comercial',1,1);
/*!40000 ALTER TABLE `categorias_propiedad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `imagenes_propiedades`
--

LOCK TABLES `imagenes_propiedades` WRITE;
/*!40000 ALTER TABLE `imagenes_propiedades` DISABLE KEYS */;
INSERT INTO `imagenes_propiedades` VALUES (24,5,'Uploads/propiedades/propiedad-5-6a4cb0c6dbbfa.jpg','Departamento en San Carlos',1,1,'2026-07-07 07:54:46'),(25,4,'Uploads/propiedades/propiedad-4-6a4dbe9331962.jpg','Departamento en San Carlos',1,1,'2026-07-08 03:05:55'),(27,8,'Uploads/propiedades/propiedad-8-6a4ea9bf53bb8.jpg','AAAAA',1,3,'2026-07-08 19:49:19'),(28,8,'Uploads/propiedades/propiedad-8-6a4ea9bf5401b.jpg','AAAAA',0,4,'2026-07-08 19:49:19'),(29,8,'Uploads/propiedades/propiedad-8-6a4ea9bf543a6.jpg','AAAAA',0,5,'2026-07-08 19:49:19'),(30,8,'Uploads/propiedades/propiedad-8-6a4ea9bf54778.jpg','AAAAA',0,6,'2026-07-08 19:49:19'),(31,8,'Uploads/propiedades/propiedad-8-6a4ea9bf54ae5.jpg','AAAAA',0,7,'2026-07-08 19:49:19'),(32,8,'Uploads/propiedades/propiedad-8-6a4ea9bf54ec8.jpg','AAAAA',0,8,'2026-07-08 19:49:19'),(33,9,'Uploads/propiedades/propiedad-9-6a5c0ee0df14f.webp','Departamento en San Carlos',1,1,'2026-07-18 23:40:17');
/*!40000 ALTER TABLE `imagenes_propiedades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `mensajes_contacto`
--

LOCK TABLES `mensajes_contacto` WRITE;
/*!40000 ALTER TABLE `mensajes_contacto` DISABLE KEYS */;
INSERT INTO `mensajes_contacto` VALUES (1,4,'Carlos Ramirez','644 567 8901','carlos@inmobiliaria.com','quiero la casa','cerrado','2026-07-05 07:32:22','2026-07-05 12:52:05'),(2,4,'Carlos Ram?rez','644 567 8901','uncorreo@gmail.com','Hola papu','cerrado','2026-07-05 18:06:23','2026-07-05 12:49:56'),(3,4,'Carlos Ram?rez','644 567 8901','uncorreo@gmail.com','Hola papu','cerrado','2026-07-05 18:12:35','2026-07-05 12:49:50'),(4,5,'Ana L?pez','6449876543','carlos@inmobiliaria.com','Un mensaje','cerrado','2026-07-05 20:55:16','2026-07-05 13:56:11'),(5,5,'Carlos Ram?rez','644 567 8901','ana@inmobiliaria.com','AAAAAAAA','cerrado','2026-07-06 21:49:18','2026-07-06 14:58:48'),(6,5,'Carlos Ram?rez','6449876543','carlos@inmobiliaria.com','qaqa','cerrado','2026-07-06 21:52:54','2026-07-06 14:58:45'),(7,5,'Carlos Ram?rez','6449876543','carlos@inmobiliaria.com','assasa','cerrado','2026-07-06 21:53:20','2026-07-06 14:58:41'),(8,4,'Carlos Ram?rez','644 567 8901','ana@inmobiliaria.com','asdadfs','cerrado','2026-07-06 21:59:06','2026-07-06 22:14:25');
/*!40000 ALTER TABLE `mensajes_contacto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `propiedades`
--

LOCK TABLES `propiedades` WRITE;
/*!40000 ALTER TABLE `propiedades` DISABLE KEYS */;
INSERT INTO `propiedades` VALUES (4,3,'Departamento en San Carlos','departamento-san-carlos','Departamento c?modo cerca de zona tur?stica, ideal para descanso o renta vacacional.',7500.00,'MXN','renta','activo',0,'san_carlos','Almendro 2550','https://www.google.com/maps?q=Almendro%202550&output=embed',2,1.0,1,0.00,70.00,'2026-06-23 06:08:25','2026-07-08 03:06:42',2),(5,8,'Departamento en San Carlos','departamento-en-san-carlos-9b234d','Un baño no sirve por si acaso mijo',1500000.00,'MXN','venta','activo',0,'ciudad_obregon','Boulevard Manlio Fabio Beltrones #850, San Carlos, Sonora.','https://www.google.com/maps?q=Boulevard%20Manlio%20Fabio%20Beltrones%20%23850%2C%20San%20Carlos%2C%20Sonora.&output=embed',0,-4.0,0,20.00,24.99,'2026-06-23 20:30:01','2026-07-08 21:25:49',1),(8,7,'AAAAA','aaaaa-604a41','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since 1966, when designers at Letraset and James Mosley, the librarian at St Bride Printing Library in London, took a 1914 Cicero translation and scrambled it to make dummy text for Letraset\'s Body Type sheets. It has survived not only many decades, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised thanks to these sheets and more recently with desktop publishing software like Aldus PageMaker and Microsoft Word including versions of Lorem Ipsum.\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since 1966, when designers at Letraset and James Mosley, the librarian at St Bride Printing Library in London, took a 1914 Cicero translation and scrambled it to make dummy text for Letraset\'s Body Type sheets. It has survived not only many decades, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised thanks to these sheets and more recently with desktop publishing software like Aldus PageMaker and Microsoft Word including versions of Lorem Ipsum.\r\n\r\nWhy do we use it?\r\nIt is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).',11222.00,'MXN','traspaso','activo',0,'ciudad_obregon','Boulevard Manlio Fabio Beltrones #850, San Carlos, Sonora.','https://www.google.com/maps?q=Boulevard%20Manlio%20Fabio%20Beltrones%20%23850%2C%20San%20Carlos%2C%20Sonora.&output=embed',1,1.0,1,21.00,21.00,'2026-07-07 00:11:02','2026-07-07 05:13:44',1),(9,6,'Departamento en San Carlos','departamento-en-san-carlos-0de2a2','El mejor almacen',30000.00,'MXN','renta','activo',1,'ciudad_obregon','almendro 2550 #85020, obregon, Sonora.','https://www.google.com/maps?q=almendro%202550%20%2385020%2C%20obregon%2C%20Sonora.&output=embed',6,2.0,2,300.00,280.00,'2026-07-18 23:40:16','2026-07-18 23:40:16',3);
/*!40000 ALTER TABLE `propiedades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `usuarios_admin`
--

LOCK TABLES `usuarios_admin` WRITE;
/*!40000 ALTER TABLE `usuarios_admin` DISABLE KEYS */;
INSERT INTO `usuarios_admin` VALUES (1,'Administrador','admin@inmobiliaria.com','admin','admin',1,'2026-06-23 06:08:25','2026-07-07 07:24:30');
/*!40000 ALTER TABLE `usuarios_admin` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-18 17:44:47
