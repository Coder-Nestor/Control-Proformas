-- Tabla para almacenar múltiples documentos por entidad
-- Permite gestionar archivos PDF e imágenes para gestiones, proformas, OC, facturas y entregas

CREATE TABLE IF NOT EXISTS `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_entidad` varchar(50) NOT NULL COMMENT 'gestion, proforma, orden_compra, factura, entrega_factura',
  `id_entidad` int(11) NOT NULL COMMENT 'ID del registro que contiene el documento',
  `nombre_archivo` varchar(255) NOT NULL COMMENT 'Nombre del archivo guardado (ej: gestion_20260902_120000_abc123.pdf)',
  `nombre_original` varchar(255) COMMENT 'Nombre original del archivo antes de subida',
  `mime_type` varchar(50) COMMENT 'Tipo MIME (application/pdf, image/jpeg, etc)',
  `tamano_bytes` int(11) COMMENT 'Tamaño del archivo en bytes',
  `orden` int(11) DEFAULT 1 COMMENT 'Orden de visualización del documento',
  `creado_en` timestamp DEFAULT CURRENT_TIMESTAMP,
  `creado_por` int(11) COMMENT 'ID del usuario que subió el archivo',
  `eliminado_en` timestamp NULL COMMENT 'Soft delete timestamp',
  `eliminado_por` int(11) COMMENT 'ID del usuario que eliminó el archivo',
  PRIMARY KEY (`id`),
  KEY `idx_entidad` (`tipo_entidad`, `id_entidad`),
  KEY `idx_eliminado` (`eliminado_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
