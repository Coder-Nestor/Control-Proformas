# 📋 Guía: Múltiples Documentos por Registro

## ✅ Cambios Realizados

### 1. **Límite de Archivos**
- Modificado de **10 MB** a **5 MB** por archivo
- Compresión automática ahora usa límite de **5 MB**

### 2. **Nuevo Método en Controller**
Se agregó `handleMultipleUploads()` para procesar múltiples archivos:

```php
// Uso en un Controller:
$archivos = $this->handleMultipleUploads('documentos[]', 'gestiones');
// Retorna: ['gestion_20260902_120000_abc123.pdf', 'gestion_20260902_120001_def456.jpg']
```

### 3. **Nuevo Modelo: Documento**
Ubicación: `app/Models/Documento.php`

Métodos disponibles:
```php
// Crear registro de documento
Documento::crearDelArchivo(
    'gestion',          // tipo_entidad
    123,                // id_entidad
    'archivo.pdf',      // nombreArchivo
    'application/pdf',  // mimeType
    1048576,            // tamanoBytesDeArchivo
    1                   // creadoPor (user ID)
);

// Obtener todos los documentos de una entidad
$documentos = Documento::deEntidad('gestion', 123);
// Retorna array de documentos asociados a esa gestión

// Contar documentos
$total = Documento::contar('factura', 456);

// Eliminar documento específico
Documento::softDelete($docId, $userId);

// Eliminar todos los documentos de una entidad
Documento::eliminarDeEntidad('proforma', 789, $userId);
```

### 4. **Tabla de Base de Datos**
Se debe ejecutar el siguiente SQL para crear la tabla `documentos`:

**Archivo:** `database/migration_documentos.sql`

```sql
CREATE TABLE IF NOT EXISTS `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_entidad` varchar(50) NOT NULL,
  `id_entidad` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255),
  `mime_type` varchar(50),
  `tamano_bytes` int(11),
  `orden` int(11) DEFAULT 1,
  `creado_en` timestamp DEFAULT CURRENT_TIMESTAMP,
  `creado_por` int(11),
  `eliminado_en` timestamp NULL,
  `eliminado_por` int(11),
  PRIMARY KEY (`id`),
  KEY `idx_entidad` (`tipo_entidad`, `id_entidad`),
  KEY `idx_eliminado` (`eliminado_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 📝 Pasos para Implementar

### Paso 1: Crear la Tabla
1. Abre **phpMyAdmin**
2. Selecciona tu base de datos
3. Copia y ejecuta el SQL de `database/migration_documentos.sql`

### Paso 2: Modificar las Vistas
En cada formulario (gestiones/form.php, proformas/form.php, etc.), cambia:

**ANTES:**
```php
<input type="file" name="documento_pdf" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
```

**DESPUÉS:**
```php
<input type="file" name="documentos[]" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" multiple>
<small>Máximo 5 archivos de 5 MB cada uno</small>
```

### Paso 3: Actualizar los Controllers

#### En GestionController::store()
```php
// ANTES:
$data['documento_pdf'] = $this->handleUpload('documento_pdf', 'gestiones');
$id = Gestion::insert($data);

// DESPUÉS:
$id = Gestion::insert($data);
$archivos = $this->handleMultipleUploads('documentos[]', 'gestiones');
foreach ($archivos as $archivo) {
    Documento::crearDelArchivo('gestion', $id, $archivo, 'application/pdf', filesize(__DIR__ . '/../../public/uploads/gestiones/' . $archivo), Auth::id());
}
```

#### En GestionController::show()
```php
// ANTES:
$this->view('gestiones/show', ['gestion' => $gestion, ...]);

// DESPUÉS:
$this->view('gestiones/show', [
    'gestion' => $gestion,
    'documentos' => Documento::deEntidad('gestion', $id),
    ...
]);
```

#### En GestionController::destroy()
```php
// Al eliminar, también elimina los documentos asociados
Documento::eliminarDeEntidad('gestion', $id, Auth::id());
$this->flash('success', 'Gestión y sus documentos eliminados.');
```

### Paso 4: Actualizar las Vistas

En `app/Views/gestiones/show.php`, muestra los documentos:

```php
<?php if (!empty($documentos)): ?>
    <div class="section">
        <h3>📎 Documentos Adjuntos (<?php echo count($documentos); ?>)</h3>
        <ul>
        <?php foreach ($documentos as $doc): ?>
            <li>
                <a href="/proformas-app/public/uploads/gestiones/<?php echo htmlspecialchars($doc['nombre_archivo']); ?>" 
                   target="_blank">
                    📄 <?php echo htmlspecialchars($doc['nombre_archivo']); ?>
                </a>
                <small>(<?php echo round($doc['tamano_bytes'] / 1024, 2); ?> KB)</small>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
```

## 🔄 Migración de Datos Existentes

Si ya tienes archivos guardados en columnas como `documento_pdf`, puedes migrarlos:

```php
// Script temporal para migrar datos existentes
$gestiones = Gestion::all();
foreach ($gestiones as $g) {
    if (!empty($g['documento_pdf'])) {
        Documento::crearDelArchivo(
            'gestion',
            $g['id'],
            $g['documento_pdf'],
            'application/pdf',
            filesize(__DIR__ . '/../public/uploads/gestiones/' . $g['documento_pdf']),
            1  // user ID (ajusta según corresponda)
        );
    }
}
```

## ⚠️ Consideraciones

- **Columnas antiguas:** Puedes mantener las columnas `documento_pdf`, etc. para compatibilidad
- **Eliminación en cascada:** Al eliminar una gestión/factura, usa `Documento::eliminarDeEntidad()`
- **Permisos:** Los archivos heredan permisos de la carpeta uploads
- **Compresión:** Se aplica automáticamente en `handleMultipleUploads()`

## ✅ Checklist de Implementación

- [ ] Ejecutar SQL de migración en base de datos
- [ ] Verificar que tabla `documentos` existe
- [ ] Actualizar formularios (añadir `multiple` a inputs)
- [ ] Actualizar Controllers para usar `handleMultipleUploads()`
- [ ] Actualizar Vistas para mostrar lista de documentos
- [ ] Probar subida de múltiples archivos
- [ ] Verificar compresión funciona con archivos de 5 MB
- [ ] Verificar eliminación de documentos en cascada

## 📞 Soporte

Si hay errores:
1. Verifica que la tabla `documentos` existe en phpMyAdmin
2. Asegúrate de importar `Documento` en los Controllers: `use App\Models\Documento;`
3. Revisa permisos de carpeta uploads (debe tener permiso de escritura)
