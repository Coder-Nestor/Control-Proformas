# Sistema de Compresión Automática de Documentos

## Descripción

Este sistema comprime automáticamente los documentos (imágenes y PDFs) que se suben al sistema de gestión de proformas, manteniendo un límite máximo de **1 MB por archivo**.

## ¿Por qué comprimir?

- **Ahorro de espacio en disco**: Reduce significativamente el espacio ocupado
- **Mejor rendimiento**: Archivos más pequeños = cargas más rápidas
- **Prevención de saturación**: Evita llenar el disco rápidamente con documentos pesados
- **Automatización**: La compresión ocurre de forma transparente al usuario

## Funcionamiento

### 1. **Detección automática**

Cuando un archivo es subido:
1. Se guarda temporalmente en el servidor
2. Se verifica su tamaño
3. Si supera 1 MB, se comprime automáticamente
4. Se valida que el archivo comprimido cumpla con las restricciones

### 2. **Soporte de formatos**

**Imágenes:**
- JPEG (.jpg)
- PNG (.png)
- GIF (.gif)
- WebP (.webp)

**Documentos:**
- PDF (.pdf)

### 3. **Métodos de compresión**

#### Para imágenes:
- **Opción 1 (Preferida)**: Imagick (si está instalado)
  - Mejor calidad de compresión
  - Requiere: `php-imagick` extension
  - Comandos instalación (XAMPP):
    ```bash
    # Descarga imagick desde: https://pecl.php.net/package/imagick
    # O usa: pecl install imagick
    ```

- **Opción 2 (Fallback)**: GD Library (incluida con PHP)
  - Disponible por defecto en PHP
  - Reduce resolución y calidad
  - Funciona con todos los formatos

#### Para PDFs:
- **Opción 1 (Preferida)**: Ghostscript
  - Compresión superior para PDFs
  - Requiere: `gswin64c.exe` o `gs` en Windows/Linux
  - Descarga: https://www.ghostscript.com/download/gsdnld.html
  - Instalación: Descarga e instala, se agregará automáticamente a PATH

- **Opción 2 (Sin compresión automática)**: Sin Ghostscript
  - Los PDFs se guardan tal cual
  - Sistema mostró aviso de que Ghostscript no está disponible
  - Recomendación: instala Ghostscript para compresión automática

## Configuración

Edita `config/compression.php` para personalizar los parámetros:

```php
return [
    // Máximo tamaño permitido en bytes (1 MB por defecto)
    'max_size_bytes' => 1 * 1024 * 1024,
    
    // Calidad JPEG (1-100, 75 recomendado)
    'compression_quality' => 75,
    
    // Dimensiones máximas para imágenes
    'max_image_width' => 2000,
    'max_image_height' => 2000,
    
    // Habilitar/deshabilitar compresión
    'enabled' => true,
    
    // Debug mode para logging
    'debug' => false,
];
```

## Instalación de dependencias

### Ghostscript (recomendado para PDFs)

**Windows:**
1. Descarga desde: https://www.ghostscript.com/download/gsdnld.html
2. Instala el versión "AGPL" o "GPL"
3. Durante la instalación, selecciona agregar a PATH
4. Verifica que `gswin64c.exe` esté disponible en PATH

**Linux:**
```bash
sudo apt-get install ghostscript
```

**macOS:**
```bash
brew install ghostscript
```

### Imagick (opcional, para mejor compresión de imágenes)

**Windows (XAMPP):**
1. Descarga la extensión desde: https://pecl.php.net/package/imagick
2. Coloca los archivos `.dll` en `php\ext\`
3. Habilita en `php.ini`: `extension=imagick`

**Linux:**
```bash
sudo apt-get install php-imagick
```

## Parámetros de compresión ajustables

### Calidad JPEG
- **75** (defecto): Balance buen-calidad/tamaño
- **85+**: Mejor calidad, mayor tamaño
- **50-60**: Máxima compresión, calidad visible reducida

### Dimensiones máximas
- **Actual**: 2000x2000 px
- **Para web**: 1280x1024 px (más compresión)
- **Para impresión**: 3000x3000 px (menos compresión)

## Ejemplo de uso

### Upload de un documento de 5 MB

**Antes:**
```
proformas_20250901_123456_abc123.pdf → 5,234 KB
```

**Después (con Ghostscript):**
```
proformas_20250901_123456_abc123.pdf → 856 KB
```

Reducción de ~84% de tamaño.

### Upload de una imagen JPEG de 8 MB

**Antes:**
```
gestiones_20250901_123456_abc123.jpg → 8,192 KB
```

**Después (con GD o Imagick):**
```
gestiones_20250901_123456_abc123.jpg → 980 KB
```

Reducción de ~88% de tamaño.

## Troubleshooting

### "Ghostscript no disponible"
- **Solución 1**: Instala Ghostscript desde https://www.ghostscript.com/
- **Solución 2**: Los PDFs se guardarán sin compresión automática (considera instalar)

### Las imágenes se ven pixeladas
- **Causa**: Compresión muy agresiva
- **Solución**: Aumenta `compression_quality` en `config/compression.php` a 80-90

### Error: "No hay extensión de imagen disponible"
- **Causa**: GD ni Imagick están instalados
- **Solución**: Habilita GD en PHP (generalmente incluida) o instala Imagick

### Los archivos no se están comprimiendo
- **Verificar**: Que `enabled` en `config/compression.php` sea `true`
- **Verificar**: Que los archivos superen realmente 1 MB
- **Verificar**: Los permisos de escritura en `public/uploads/`

## Logs y debugging

Para habilitar logs de compresión:

1. En `config/compression.php`, cambia `'debug' => true;`
2. En `core/Controller.php`, descomenta la línea:
   ```php
   error_log("Archivo comprimido: {$filename} ({$sizeMsg})");
   ```
3. Revisa los logs en `logs/` o el log de PHP

## Rendimiento esperado

### Tamaño típico después de compresión

| Tipo de archivo | Tamaño original | Tamaño comprimido | Reducción |
|---|---|---|---|
| PDF escaneado (5 MB) | 5,120 KB | 800-1,000 KB | 80-85% |
| Imagen JPEG (3 MB) | 3,072 KB | 500-800 KB | 75-85% |
| Imagen PNG (2 MB) | 2,048 KB | 400-600 KB | 70-80% |
| PDF nativo | Variable | Variable | 30-60% |

## Preguntas frecuentes

**P: ¿Se pierden datos al comprimir?**
R: No. Las imágenes usan compresión JPEG con calidad 75 (muy legible). PDFs se comprimen sin pérdida cuando es posible.

**P: ¿Puedo cambiar el límite de 1 MB?**
R: Sí, edita `max_size_bytes` en `config/compression.php`.

**P: ¿Qué pasa si el archivo no se puede comprimir?**
R: Se guarda tal cual. El sistema solo comprime si puede hacerlo.

**P: ¿Afecta el rendimiento de la aplicación?**
R: Mínimamente. La compresión ocurre una sola vez al upload.

## Archivos modificados

- ✅ `core/Compressor.php` (nuevo) - Clase de compresión
- ✅ `core/Controller.php` - Integración de compresión
- ✅ `config/compression.php` (nuevo) - Configuración

## Soporte

Para problemas o mejoras, revisa:
- Los logs en `logs/`
- El archivo `error_log` de PHP
- La consola del navegador (F12)
