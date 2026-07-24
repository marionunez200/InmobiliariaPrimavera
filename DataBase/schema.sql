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
-- Dumping data for table `agentes`
--

LOCK TABLES `agentes` WRITE;
/*!40000 ALTER TABLE `agentes` DISABLE KEYS */;
INSERT INTO `agentes` VALUES (3,'Maria Fernandez','644 567 8901','uncorreo@gmail.com','Uploads/agentes/agente-6a501d611e3cf.webp',0,'2026-06-23 18:47:25','2026-07-09 22:14:57'),(4,'Mario Rodriguez','644 567 8901','uncorreo@gmail.com','Uploads/agentes/agente-6a501d5506cc8.jpg',0,'2026-06-23 18:48:08','2026-07-09 22:14:45'),(7,'Mario Pérez','644 567 8901','uncorreo@gmail.com','Uploads/agentes/agente-6a501d4ea8ccb.webp',1,'2026-06-23 22:22:14','2026-07-09 23:16:36'),(11,'Ana López','6449876543','uncorreo@gmail.com','Uploads/agentes/agente-6a4cb067637f2.webp',1,'2026-07-07 07:53:11','2026-07-09 22:16:05'),(12,'Carlos Eduardo','97898778','carlos@inmobiliaria.com','Uploads/agentes/agente-6a580d6f00c28.webp',1,'2026-07-15 22:45:03','2026-07-23 03:59:42');
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
INSERT INTO `imagenes_propiedades` VALUES (58,23,'Uploads/propiedades/propiedad-23-6a617abb79607.webp','Departamento en San Carlos',1,1,'2026-07-23 02:21:47'),(59,23,'Uploads/propiedades/propiedad-23-6a617abbaea5c.webp','Departamento en San Carlos',0,2,'2026-07-23 02:21:47'),(60,23,'Uploads/propiedades/propiedad-23-6a617abbd98d8.webp','Departamento en San Carlos',0,3,'2026-07-23 02:21:48'),(61,23,'Uploads/propiedades/propiedad-23-6a617abc104d1.webp','Departamento en San Carlos',0,4,'2026-07-23 02:21:48'),(62,24,'Uploads/propiedades/propiedad-24-6a617ae1a97c7.webp','Departamento en Obregón',1,1,'2026-07-23 02:22:25'),(63,24,'Uploads/propiedades/propiedad-24-6a617ae1c5fa5.webp','Departamento en Obregón',0,2,'2026-07-23 02:22:25'),(64,25,'Uploads/propiedades/propiedad-25-6a617b12d17eb.webp','Local comercial en Guaymas',1,1,'2026-07-23 02:23:15'),(65,25,'Uploads/propiedades/propiedad-25-6a617b1306b70.webp','Local comercial en Guaymas',0,2,'2026-07-23 02:23:15'),(66,25,'Uploads/propiedades/propiedad-25-6a617b133aa7b.webp','Local comercial en Guaymas',0,3,'2026-07-23 02:23:15'),(67,26,'Uploads/propiedades/propiedad-26-6a617b41545c0.webp','Departamento en Obregón',1,1,'2026-07-23 02:24:01'),(68,26,'Uploads/propiedades/propiedad-26-6a617b4189754.webp','Departamento en Obregón',0,2,'2026-07-23 02:24:01'),(69,26,'Uploads/propiedades/propiedad-26-6a617b41b7a9d.webp','Departamento en Obregón',0,3,'2026-07-23 02:24:01'),(70,26,'Uploads/propiedades/propiedad-26-6a617b41e7368.webp','Departamento en Obregón',0,4,'2026-07-23 02:24:02'),(71,26,'Uploads/propiedades/propiedad-26-6a617b420deb6.webp','Departamento en Obregón',0,5,'2026-07-23 02:24:02'),(72,27,'Uploads/propiedades/propiedad-27-6a617b617dd92.webp','AAAAA',1,1,'2026-07-23 02:24:33'),(73,27,'Uploads/propiedades/propiedad-27-6a617b61aa76b.webp','AAAAA',0,2,'2026-07-23 02:24:33'),(74,27,'Uploads/propiedades/propiedad-27-6a617b61d6427.webp','AAAAA',0,3,'2026-07-23 02:24:33'),(75,27,'Uploads/propiedades/propiedad-27-6a617b61f416f.webp','AAAAA',0,4,'2026-07-23 02:24:34'),(76,27,'Uploads/propiedades/propiedad-27-6a617b621ad0a.webp','AAAAA',0,5,'2026-07-23 02:24:34'),(77,28,'Uploads/propiedades/propiedad-28-6a617bb285a4a.webp','LOL',1,1,'2026-07-23 02:25:54'),(78,29,'Uploads/propiedades/propiedad-29-6a617bd09ade5.webp','Departamento en Obregón',1,1,'2026-07-23 02:26:24'),(79,30,'Uploads/propiedades/propiedad-30-6a617be771964.webp','Departamento en San Carlos',1,1,'2026-07-23 02:26:47'),(80,31,'Uploads/propiedades/propiedad-31-6a617bffce649.webp','Local comercial en Guaymas',1,1,'2026-07-23 02:27:12'),(81,32,'Uploads/propiedades/propiedad-32-6a617c1ea625d.webp','AAAAA',1,1,'2026-07-23 02:27:42'),(82,33,'Uploads/propiedades/propiedad-33-6a617c4629995.webp','Departamento en Obregón',1,1,'2026-07-23 02:28:22'),(83,34,'Uploads/propiedades/propiedad-34-6a617c615a182.webp','AAAAA',1,1,'2026-07-23 02:28:49'),(84,35,'Uploads/propiedades/propiedad-35-6a617c79a87cf.webp','Local comercial en Guaymas',1,1,'2026-07-23 02:29:13'),(85,33,'Uploads/propiedades/propiedad-33-6a62a9a8cda43.webp','Departamento en Obregón',0,2,'2026-07-23 23:54:17'),(86,33,'Uploads/propiedades/propiedad-33-6a62a9a909b3f.webp','Departamento en Obregón',0,3,'2026-07-23 23:54:17'),(87,33,'Uploads/propiedades/propiedad-33-6a62a9a91d2c2.webp','Departamento en Obregón',0,4,'2026-07-23 23:54:17'),(88,33,'Uploads/propiedades/propiedad-33-6a62a9a9379fd.webp','Departamento en Obregón',0,5,'2026-07-23 23:54:17'),(89,33,'Uploads/propiedades/propiedad-33-6a62a9a966519.webp','Departamento en Obregón',0,6,'2026-07-23 23:54:17'),(90,32,'Uploads/propiedades/propiedad-32-6a62a9cc44f7f.webp','Terreno en venta en Guaymas',0,2,'2026-07-23 23:54:52'),(91,32,'Uploads/propiedades/propiedad-32-6a62a9cc71bdd.webp','Terreno en venta en Guaymas',0,3,'2026-07-23 23:54:52'),(92,32,'Uploads/propiedades/propiedad-32-6a62a9cc99743.webp','Terreno en venta en Guaymas',0,4,'2026-07-23 23:54:52'),(93,32,'Uploads/propiedades/propiedad-32-6a62a9ccc5440.webp','Terreno en venta en Guaymas',0,5,'2026-07-23 23:54:52'),(94,32,'Uploads/propiedades/propiedad-32-6a62a9ccf0c2a.webp','Terreno en venta en Guaymas',0,6,'2026-07-23 23:54:53'),(95,28,'Uploads/propiedades/propiedad-28-6a62aa00ec9ac.webp','Almacén en San Carlos',0,2,'2026-07-23 23:55:45'),(96,28,'Uploads/propiedades/propiedad-28-6a62aa010b166.webp','Almacén en San Carlos',0,3,'2026-07-23 23:55:45'),(97,28,'Uploads/propiedades/propiedad-28-6a62aa012552e.webp','Almacén en San Carlos',0,4,'2026-07-23 23:55:45'),(98,28,'Uploads/propiedades/propiedad-28-6a62aa013f2b0.webp','Almacén en San Carlos',0,5,'2026-07-23 23:55:45'),(99,28,'Uploads/propiedades/propiedad-28-6a62aa0157693.webp','Almacén en San Carlos',0,6,'2026-07-23 23:55:45'),(100,28,'Uploads/propiedades/propiedad-28-6a62aa016f95e.webp','Almacén en San Carlos',0,7,'2026-07-23 23:55:45');
/*!40000 ALTER TABLE `imagenes_propiedades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `intentos_login`
--

LOCK TABLES `intentos_login` WRITE;
/*!40000 ALTER TABLE `intentos_login` DISABLE KEYS */;
/*!40000 ALTER TABLE `intentos_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `mensajes_contacto`
--

LOCK TABLES `mensajes_contacto` WRITE;
/*!40000 ALTER TABLE `mensajes_contacto` DISABLE KEYS */;
INSERT INTO `mensajes_contacto` VALUES (19,34,'Karla Pérez','6445798656','karla@gmail.com','Me interesa la propiedad, me gustaría recibir más información.','nuevo','2026-07-24 00:00:17',NULL);
/*!40000 ALTER TABLE `mensajes_contacto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,1,'$2y$10$SLUPAHVXaFlMOC0eL1TPp.sCYoVvQXo6Ktb.quemlH1qVcWCBNKay','2026-07-22 22:13:10',0,'2026-07-22 19:13:10'),(2,1,'$2y$10$XusrVLrT/H6wDBKQH097b.4RfYzmFHQR.ExlHWQ6JvMYK/5z8Oqia','2026-07-22 22:13:16',0,'2026-07-22 19:13:16'),(3,1,'$2y$10$dcGqVILsIeYQe.fCLx49nuZBKAmaEAmyReQhtyq.1P0.yTMByP4OG','2026-07-22 22:15:18',0,'2026-07-22 19:15:18'),(4,1,'$2y$10$GsUnZ3xyYVHEnM5HJi8EWOuXpRkP15cs3PaRegY2Osd54B1YJRcaO','2026-07-22 22:18:02',0,'2026-07-22 19:18:02'),(5,1,'$2y$10$mVB8tvqcir6ttglkpRHw6OZCN7HSEyuItJY53rBqtGGfRO4fx1ttC','2026-07-22 22:22:41',0,'2026-07-22 19:22:41'),(6,1,'e2c3bc567c7522d5e1938699bd56a8087f51eed34efc97c9b5b01fe9bba10b60','2026-07-22 22:30:08',0,'2026-07-22 19:30:08'),(7,1,'b50bbc4ca8df1ef5aa55d182398e37d6ec3acd903ff070b1edf6370c0e03711a','2026-07-22 22:30:17',0,'2026-07-22 19:30:17'),(8,1,'a722a273cae22535437964d4ba68dfc1ea9e1c9eabd159f80b11a75b9280491c','2026-07-22 23:10:26',0,'2026-07-22 20:10:26'),(9,1,'370195df68dc7147adcd9d243a2bf98b960b61aeffda64c6e6b0195e5637c145','2026-07-22 23:11:42',0,'2026-07-22 20:11:42'),(10,1,'dd9617279a8a526f25f1de58eedde32d69e3fe0e8434a564b57bb435269ab2ac','2026-07-22 23:12:39',0,'2026-07-22 20:12:39'),(11,1,'ecbcd6ab876e1fef2332ca0a5d25e2777757a3dd65e1b5c728ca267b24fa2295','2026-07-22 23:13:18',1,'2026-07-22 20:13:18'),(12,1,'8f51f0cd07cad1cdc29864f045bc9f6e8acc7246871b0db84be2035292377c03','2026-07-22 23:14:11',1,'2026-07-22 20:14:11'),(13,1,'e4e30f01c238ca5db0a46c16d94e57c9317b3ab1b5812a2d186124c999ffee71','2026-07-22 23:24:04',1,'2026-07-22 20:24:04'),(14,1,'453caf38c0582e1ecfaf7e30c1f358da236b8c3c78db00865fa1d4986f36baf3','2026-07-22 23:29:39',0,'2026-07-22 20:29:39'),(15,1,'1a2eb23fc9c5b2bfd1dd153298b17e01da7028b1876afdc063e54f444eb64908','2026-07-23 01:23:15',1,'2026-07-22 22:23:15');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `propiedades`
--

LOCK TABLES `propiedades` WRITE;
/*!40000 ALTER TABLE `propiedades` DISABLE KEYS */;
INSERT INTO `propiedades` VALUES (23,12,'Departamento en renta en San Carlos','departamento-en-san-carlos-b779da','',12000.00,'MXN','renta','activo',0,'san_carlos','Boulevard Manlio Fabio Beltrones #850, San Carlos, Sonora.','https://www.google.com/maps?q=Boulevard%20Manlio%20Fabio%20Beltrones%20%23850%2C%20San%20Carlos%2C%20Sonora.&output=embed',1,1.0,1,1.00,1.00,'2026-07-23 02:21:47','2026-07-23 23:58:37',3),(24,12,'Departamento en Obregón','departamento-en-obregon-1a8f76','',54111.00,'MXN','venta','activo',0,'ciudad_obregon','Av. Serdán #210, Guaymas, Sonora.','https://www.google.com/maps?q=Av.%20Serd%C3%A1n%20%23210%2C%20Guaymas%2C%20Sonora.&output=embed',1,1.0,3,2.00,5.00,'2026-07-23 02:22:25','2026-07-23 04:01:46',3),(25,7,'Local comercial en Guaymas','local-comercial-en-guaymas-2d10c2','',4500000.00,'MXN','traspaso','activo',0,'guaymas','Av. Serdán #210, Guaymas, Sonora.','https://www.google.com/maps?q=Av.%20Serd%C3%A1n%20%23210%2C%20Guaymas%2C%20Sonora.&output=embed',2,1.0,1,1.00,1.00,'2026-07-23 02:23:14','2026-07-23 23:57:47',5),(26,12,'Departamento en Obregón','departamento-en-obregon-153c5e','',51111.00,'MXN','traspaso','activo',0,'ciudad_obregon','Av. Serdán #210, Guaymas, Sonora.','https://www.google.com/maps?q=Av.%20Serd%C3%A1n%20%23210%2C%20Guaymas%2C%20Sonora.&output=embed',0,0.0,0,1.00,1.00,'2026-07-23 02:24:01','2026-07-23 23:56:53',3),(27,11,'Casa en venta en Navojoa','aaaaa-17d4ae','',5000000.00,'MXN','venta','activo',0,'navojoa','Mont L blanc #2123, Obregon, Sonora.','https://www.google.com/maps?q=Mont%20L%20blanc%20%232123%2C%20Obregon%2C%20Sonora.&output=embed',0,1.0,1,1.00,1.00,'2026-07-23 02:24:33','2026-07-23 23:57:05',2),(28,11,'Almacén en San Carlos','lol-285061','',5000.00,'MXN','renta','activo',0,'san_carlos','Mont L blanc #2123, Obregon, Sonora.','https://www.google.com/maps?q=Mont%20L%20blanc%20%232123%2C%20Obregon%2C%20Sonora.&output=embed',1,1.0,1,1.00,1.00,'2026-07-23 02:25:54','2026-07-23 23:57:22',1),(29,11,'Departamento en Obregón','departamento-en-obregon-09a871','',98965.00,'MXN','venta','activo',0,'ciudad_obregon','Mont L blanc #2123, Obregon, Sonora.','https://www.google.com/maps?q=Mont%20L%20blanc%20%232123%2C%20Obregon%2C%20Sonora.&output=embed',0,0.0,0,1.00,1.00,'2026-07-23 02:26:24','2026-07-23 04:01:18',3),(30,12,'Departamento en San Carlos','departamento-en-san-carlos-770e15','',8485.00,'MXN','traspaso','activo',0,'ciudad_obregon','Mont L blanc #2123, Obregon, Sonora.','https://www.google.com/maps?q=Mont%20L%20blanc%20%232123%2C%20Obregon%2C%20Sonora.&output=embed',1,1.0,1,2.00,1.00,'2026-07-23 02:26:47','2026-07-23 23:55:04',3),(31,12,'Local comercial en Guaymas','local-comercial-en-guaymas-fce1c5','',879987.00,'MXN','venta','activo',0,'ciudad_obregon','Boulevard Manlio Fabio Beltrones #850, San Carlos, Sonora.','https://www.google.com/maps?q=Boulevard%20Manlio%20Fabio%20Beltrones%20%23850%2C%20San%20Carlos%2C%20Sonora.&output=embed',1,1.0,1,1.00,1.00,'2026-07-23 02:27:11','2026-07-23 22:15:20',5),(32,7,'Terreno en venta en Guaymas','aaaaa-ea5906','',5000000.00,'MXN','venta','activo',0,'guaymas','Av. Serdán #210, Guaymas, Sonora.','https://www.google.com/maps?q=Av.%20Serd%C3%A1n%20%23210%2C%20Guaymas%2C%20Sonora.&output=embed',1,1.0,1,1.00,1.00,'2026-07-23 02:27:42','2026-07-23 23:54:52',4),(33,12,'Departamento en Obregón','departamento-en-obregon-6294b1','',78666.00,'MXN','venta','activo',0,'ciudad_obregon','Av. Serdán #210, Guaymas, Sonora.','https://www.google.com/maps?q=Av.%20Serd%C3%A1n%20%23210%2C%20Guaymas%2C%20Sonora.&output=embed',0,0.0,0,1.00,1.00,'2026-07-23 02:28:22','2026-07-23 23:54:16',3),(34,11,'Casa en venta en Obregón','aaaaa-159c9d','',810000.00,'MXN','venta','activo',0,'ciudad_obregon','Boulevard Manlio Fabio Beltrones #850, San Carlos, Sonora.','https://www.google.com/maps?q=Boulevard%20Manlio%20Fabio%20Beltrones%20%23850%2C%20San%20Carlos%2C%20Sonora.&output=embed',0,0.0,0,3.00,4.00,'2026-07-23 02:28:49','2026-07-23 23:53:58',2),(35,7,'Local comercial en Guaymas','local-comercial-en-guaymas-9a805c','',44545.00,'MXN','venta','activo',0,'ciudad_obregon','Av. Serdán #210, Guaymas, Sonora.','https://www.google.com/maps?q=Av.%20Serd%C3%A1n%20%23210%2C%20Guaymas%2C%20Sonora.&output=embed',1,1.0,1,1.00,1.00,'2026-07-23 02:29:13','2026-07-23 22:14:54',2);
/*!40000 ALTER TABLE `propiedades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `usuarios_admin`
--

LOCK TABLES `usuarios_admin` WRITE;
/*!40000 ALTER TABLE `usuarios_admin` DISABLE KEYS */;
INSERT INTO `usuarios_admin` VALUES (1,'Administrador','carlosaguero2257@gmail.com','$2y$10$fKNUn86aj0lh9Yg9272dOuByk4vDc5fMAeX4zQN4rSKfsBv0RHFSy','admin',1,'2026-06-23 06:08:25','2026-07-22 22:23:34');
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

-- Dump completed on 2026-07-23 17:24:32
