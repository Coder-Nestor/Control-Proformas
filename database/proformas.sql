-- MySQL dump 10.13  Distrib 8.3.0, for Win64 (x86_64)
--
-- Host: localhost    Database: proformas
-- ------------------------------------------------------
-- Server version	5.7.17-log

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

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` VALUES (1,'Logística',1),(2,'Finca',1),(3,'Operaciones',1),(4,'IT',1),(6,'Aud. Prod. Agrícola',1);
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entregas_factura`
--

DROP TABLE IF EXISTS `entregas_factura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entregas_factura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `factura_id` int(11) NOT NULL,
  `fecha_entrega_dueno` date DEFAULT NULL,
  `fecha_solicitud_revision_pago` date DEFAULT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `documento_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `factura_id` (`factura_id`),
  KEY `creado_por` (`creado_por`),
  KEY `entregas_factura_eliminado_por_fk` (`eliminado_por`),
  CONSTRAINT `entregas_factura_eliminado_por_fk` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entregas_factura_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entregas_factura_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entregas_factura`
--

LOCK TABLES `entregas_factura` WRITE;
/*!40000 ALTER TABLE `entregas_factura` DISABLE KEYS */;
/*!40000 ALTER TABLE `entregas_factura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_compra_id` int(11) NOT NULL,
  `n_factura` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_entrega_factura` date DEFAULT NULL,
  `estado` enum('correcta','pendiente','con_problema') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `documento_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orden_compra_id` (`orden_compra_id`),
  KEY `creado_por` (`creado_por`),
  KEY `facturas_eliminado_por_fk` (`eliminado_por`),
  CONSTRAINT `facturas_eliminado_por_fk` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE,
  CONSTRAINT `facturas_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gestiones`
--

DROP TABLE IF EXISTS `gestiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gestiones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) DEFAULT NULL,
  `solicitado_por` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aprobado_por` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_aprobacion_trabajo` date DEFAULT NULL,
  `fecha_finalizacion_trabajo` date DEFAULT NULL,
  `n_cotizacion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_revision_cotizacion` date DEFAULT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `documento_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `creado_por` (`creado_por`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_n_cotizacion` (`n_cotizacion`),
  KEY `gestiones_eliminado_por_fk` (`eliminado_por`),
  CONSTRAINT `gestiones_eliminado_por_fk` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gestiones_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gestiones_ibfk_3` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gestiones`
--

LOCK TABLES `gestiones` WRITE;
/*!40000 ALTER TABLE `gestiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `gestiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial`
--

DROP TABLE IF EXISTS `historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entidad` enum('gestion','proforma','oc','factura','entrega') COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_entidad` (`entidad`,`entidad_id`),
  CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial`
--

LOCK TABLES `historial` WRITE;
/*!40000 ALTER TABLE `historial` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_compra`
--

DROP TABLE IF EXISTS `ordenes_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proforma_id` int(11) NOT NULL,
  `fecha_envio_oce` date DEFAULT NULL,
  `n_oce_interna` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('correcta','pendiente','con_problema') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `documento_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proforma_id` (`proforma_id`),
  KEY `creado_por` (`creado_por`),
  KEY `ordenes_compra_eliminado_por_fk` (`eliminado_por`),
  CONSTRAINT `ordenes_compra_eliminado_por_fk` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_compra_ibfk_1` FOREIGN KEY (`proforma_id`) REFERENCES `proformas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ordenes_compra_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_compra`
--

LOCK TABLES `ordenes_compra` WRITE;
/*!40000 ALTER TABLE `ordenes_compra` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'gestiones.ver','Gestiones','Ver el listado y el detalle de gestiones'),(2,'gestiones.crear','Gestiones','Crear una gestión nueva'),(3,'gestiones.editar','Gestiones','Editar una gestión existente'),(4,'gestiones.eliminar','Gestiones','Eliminar una gestión'),(5,'proformas.ver','Proformas','Ver el listado y el detalle de proformas'),(6,'proformas.crear','Proformas','Crear una proforma nueva'),(7,'proformas.editar','Proformas','Editar una proforma existente'),(8,'proformas.eliminar','Proformas','Eliminar una proforma'),(9,'ordenes.ver','Órdenes de compra','Ver el listado y el detalle de órdenes de compra'),(10,'ordenes.crear','Órdenes de compra','Crear una orden de compra nueva'),(11,'ordenes.editar','Órdenes de compra','Editar una orden de compra existente'),(12,'ordenes.eliminar','Órdenes de compra','Eliminar una orden de compra'),(13,'facturas.ver','Facturas','Ver el listado y el detalle de facturas'),(14,'facturas.crear','Facturas','Registrar una factura nueva'),(15,'facturas.editar','Facturas','Editar una factura existente'),(16,'facturas.eliminar','Facturas','Eliminar una factura'),(17,'entregas.ver','Entregas','Ver el listado y el detalle de entregas'),(18,'entregas.crear','Entregas','Registrar una entrega nueva'),(19,'entregas.editar','Entregas','Editar una entrega existente'),(20,'entregas.eliminar','Entregas','Eliminar una entrega'),(21,'proveedores.ver','Proveedores','Ver el catálogo de proveedores'),(22,'proveedores.crear','Proveedores','Crear un nuevo proveedor'),(23,'proveedores.eliminar','Proveedores','Eliminar un proveedor'),(24,'areas.ver','Áreas','Ver el catálogo de áreas'),(25,'areas.gestionar','Áreas','Agregar y editar áreas'),(26,'areas.eliminar','Áreas','Eliminar un área'),(27,'usuarios.ver','Usuarios','Ver el listado de usuarios'),(28,'usuarios.crear','Usuarios','Crear un usuario nuevo'),(29,'usuarios.eliminar','Usuarios','Eliminar un usuario'),(30,'historial.ver','Historial','Ver la bitácora de auditoría del sistema'),(31,'roles.gestionar','Roles y Permisos','Crear roles y asignarles permisos (siempre restringido a Administrador)'),(32,'proveedores.editar','Proveedores','Editar información de proveedores'),(33,'usuarios.editar','Usuarios','Editar información y roles de usuarios'),(34,'proveedores.activar','Proveedores','Activar/desactivar proveedores y pase a proforma'),(35,'usuarios.activar','Usuarios','Activar/desactivar cuentas de usuario');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proformas`
--

DROP TABLE IF EXISTS `proformas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proformas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) DEFAULT NULL,
  `fecha_solicitud` date DEFAULT NULL,
  `solicitado_por` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trabajo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `n_cotizacion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_cotizacion` decimal(12,2) DEFAULT NULL,
  `n_proforma` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_proforma` decimal(12,2) DEFAULT NULL,
  `fecha_revision_proforma` date DEFAULT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `documento_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `creado_por` (`creado_por`),
  KEY `idx_n_proforma` (`n_proforma`),
  KEY `proformas_eliminado_por_fk` (`eliminado_por`),
  CONSTRAINT `proformas_eliminado_por_fk` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `proformas_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `proformas_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proformas`
--

LOCK TABLES `proformas` WRITE;
/*!40000 ALTER TABLE `proformas` DISABLE KEYS */;
/*!40000 ALTER TABLE `proformas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `habilitado_proforma` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proveedores_eliminado_por_fk` (`eliminado_por`),
  CONSTRAINT `proveedores_eliminado_por_fk` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Ares Sun',1,1,'2026-09-08 00:00:00','2026-09-08 00:00:00'),(2,'CIT',1,1,'2026-09-08 00:00:00','2026-09-08 00:00:00');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_permisos`
--

DROP TABLE IF EXISTS `rol_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_permisos` (
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL,
  PRIMARY KEY (`rol_id`,`permiso_id`),
  KEY `rol_permisos_permiso_fk` (`permiso_id`),
  CONSTRAINT `rol_permisos_permiso_fk` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rol_permisos_rol_fk` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permisos`
--

LOCK TABLES `rol_permisos` WRITE;
/*!40000 ALTER TABLE `rol_permisos` DISABLE KEYS */;
INSERT INTO `rol_permisos` VALUES (1,1),(2,1),(3,1),(4,1),(1,2),(2,2),(3,2),(1,3),(2,3),(3,3),(1,4),(2,4),(1,5),(2,5),(3,5),(4,5),(1,6),(2,6),(3,6),(1,7),(2,7),(3,7),(1,8),(2,8),(1,9),(2,9),(4,9),(1,10),(2,10),(1,11),(2,11),(1,12),(1,13),(2,13),(4,13),(1,14),(2,14),(1,15),(2,15),(1,16),(1,17),(2,17),(4,17),(1,18),(2,18),(1,19),(2,19),(1,20),(1,21),(2,21),(4,21),(1,22),(2,22),(1,23),(1,24),(2,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31),(1,32),(2,32),(1,33),(1,34),(2,34),(3,34),(1,35);
/*!40000 ALTER TABLE `rol_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'administrador','Administrador'),(2,'auditoria','Auditoría / Producción Agrícola'),(3,'solicitante','Solicitante de área'),(4,'lectura','Solo lectura');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajos`
--

DROP TABLE IF EXISTS `trabajos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trabajos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gestion_id` int(11) NOT NULL,
  `proforma_id` int(11) DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(12,2) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gestion` (`gestion_id`),
  KEY `idx_proforma` (`proforma_id`),
  CONSTRAINT `trabajos_ibfk_1` FOREIGN KEY (`gestion_id`) REFERENCES `gestiones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trabajos_ibfk_2` FOREIGN KEY (`proforma_id`) REFERENCES `proformas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajos`
--

LOCK TABLES `trabajos` WRITE;
/*!40000 ALTER TABLE `trabajos` DISABLE KEYS */;
/*!40000 ALTER TABLE `trabajos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` int(11) NOT NULL,
  `area` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (0,'Administrador','admin@admin.com','$2y$10$NG5WrXnCXdBpM3x4/.v56.pUyyCSJaPlaSV07q.VDcRxWZq4.soj.',1,NULL,1,'2026-08-21 19:53:22'),(3,'Patrick Aplicano','paplicano@cahsa.hn','$2y$10$H8/jm60dXzZJRMovvz2.YuY5y9yn/4Ojr2tZ6QX9Ed5zB0fgxY15W',2,'Aud. Prod. Agrícola',1,'2026-08-21 19:54:52');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-24 11:35:44
