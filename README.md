# Control de Proformas y Facturas

Aplicación web en **PHP puro con arquitectura MVC** (sin Composer ni frameworks
externos) para dar seguimiento al proceso: **Gestión → Proforma → Orden de
Compra → Factura → Entrega → Auditoría Interna**.

Diseñada para correr en un **servidor local detrás de un firewall Fortinet**:
todas las librerías (Bootstrap 5, Bootstrap Icons, Chart.js) están **alojadas
dentro del propio proyecto** en `public/assets/vendor/`. Ninguna vista apunta
a un CDN externo (googleapis, jsdelivr, unpkg, etc.), así que la app funciona
aunque el firewall bloquee la salida a internet.

## 1. Requisitos

- PHP 8.1 o superior, con extensión **PDO MySQL** habilitada.
- MySQL o MariaDB.
- Apache con `mod_rewrite` habilitado (XAMPP, WAMP, Laragon o Apache nativo
  en Linux/Windows sirven perfecto).

## 2. Estructura del proyecto

```
proformas-app/
├── app/
│   ├── Controllers/     Controladores (lógica de cada módulo)
│   ├── Models/          Modelos (acceso a datos vía PDO)
│   └── Views/           Vistas PHP (layout + páginas por módulo)
├── config/
│   ├── config.php       Credenciales de BD, URL de la app, etc.
│   └── routes.php       Tabla de rutas -> controlador -> roles permitidos
├── core/                Router, Auth, Database, Controller/Model base, helpers
├── database/
│   └── schema.sql       Esquema completo + datos iniciales (roles, admin, áreas)
├── public/              DOCUMENT ROOT del servidor web
│   ├── index.php        Front controller (único punto de entrada)
│   ├── .htaccess        Reescritura de URLs hacia index.php
│   └── assets/
│       ├── css/app.css  Estilos propios
│       ├── js/app.js    JS propio (sidebar + inicialización de Chart.js)
│       └── vendor/      Bootstrap, Bootstrap Icons y Chart.js AUTOALOJADOS
└── logs/
```

**Importante:** el *document root* de Apache debe apuntar a la carpeta
`public/`, no a la raíz del proyecto. Así el código de `app/`, `core/` y
`config/` (con las credenciales de BD) queda fuera del alcance del navegador.

## 3. Instalación paso a paso

### 3.1. Copiar el proyecto al servidor

Copia toda la carpeta `proformas-app/` a tu servidor local, por ejemplo:

- XAMPP (Windows): `C:\xampp\htdocs\proformas-app`
- Apache (Linux): `/var/www/proformas-app`

### 3.2. Crear la base de datos

Importa el esquema con phpMyAdmin, HeidiSQL, DBeaver o por consola:

```bash
mysql -u root -p < database/schema.sql
```

Esto crea la base `control_proformas`, todas las tablas, los roles, las
áreas (Logística, Finca, Operaciones, IT, Central, Aud. Prod. Agrícola), un
proveedor de ejemplo y **un usuario administrador**:

- **Correo:** `admin@empresa.com`
- **Contraseña:** `Admin123!`

⚠️ **Cambia esta contraseña** apenas inicies sesión (menú *Usuarios > Editar*).

### 3.3. Configurar la conexión a la base de datos

Edita `config/config.php` con los datos de tu servidor MySQL local:

```php
'db' => [
    'host'     => '127.0.0.1',
    'database' => 'control_proformas',
    'username' => 'root',
    'password' => 'tu_password',
],
```

Y ajusta la URL base según cómo publiques el sitio en Apache:

```php
'app' => [
    'url' => 'http://localhost/proformas-app/public',
    // o, si configuraste un VirtualHost dedicado:
    // 'url' => 'http://proformas.miempresa.local',
],
```

### 3.4. Configurar Apache (VirtualHost recomendado)

Ejemplo de VirtualHost apuntando directo a `public/`:

```apache
<VirtualHost *:80>
    ServerName proformas.miempresa.local
    DocumentRoot "C:/xampp/htdocs/proformas-app/public"

    <Directory "C:/xampp/htdocs/proformas-app/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Si usas la ruta directa (`http://localhost/proformas-app/public`) sin
VirtualHost, no necesitas nada adicional: el `.htaccess` incluido ya reescribe
las URLs internamente.

### 3.5. Verificar mod_rewrite

```bash
a2enmod rewrite
service apache2 restart
```

En XAMPP/WAMP normalmente ya viene habilitado.

### 3.6. Probar

Abre `http://localhost/proformas-app/public/login` e inicia sesión con el
usuario administrador. Deberías ver el **dashboard** con las tarjetas de KPI
y los 3 gráficos (Chart.js, cargado localmente, sin internet).

## 4. Roles del sistema

| Rol            | Permisos                                                             |
|----------------|-----------------------------------------------------------------------|
| Administrador  | Acceso total: usuarios, proveedores, solicitudes, eliminar registros |
| Auditoría      | Crear/editar solicitudes, avanzar etapas, ver proveedores            |
| Solicitante    | Crear/editar sus solicitudes (gestión y proforma)                    |
| Solo lectura   | Solo puede ver dashboard, listado y reportes                         |

Los roles se administran desde *Usuarios* (solo el rol Administrador ve ese
menú) y se definen en la tabla `roles` de la base de datos.

## 5. El modelo del negocio (Gestiones → Proformas → Órdenes de Compra)

Refleja exactamente la lógica del Excel original:

1. **Gestión** — un trabajo realizado por un proveedor. Se crea primero, con
   su cotización, fechas y (opcionalmente) un PDF escaneado. Al crear o
   editar una Gestión, eliges a qué **Proforma** pertenece (puede quedar
   "Sin asignar" hasta que la proforma exista).
2. **Proforma** — agrupa una o varias Gestiones bajo un mismo N° de
   Proforma. Se crea de forma independiente; luego vas a cada Gestión y la
   asocias a ella. Su ficha de detalle muestra todas las Gestiones
   incluidas.
3. **Orden de Compra (OC)** — relación **1 a 1** con una Proforma (igual que
   en el Excel). Al crearla eliges la Proforma (solo aparecen las que aún
   no tienen OC). Calcula automáticamente los días entre la revisión de la
   proforma y el envío de la OCE.

Cada uno de los 3 módulos tiene **CRUD completo** (ver, editar, eliminar) y
su propio campo de **PDF escaneado** independiente (cotización, proforma y
orden de compra por separado), guardado en `public/uploads/<módulo>/`.

Si eliminas una Proforma, las Gestiones que tenía asociadas **no se
borran**: quedan automáticamente "sin asignar" para poder reasignarlas. Si
eliminas una Proforma que ya tenía una Orden de Compra, esa OC se elimina
junto con ella (para evitar una OC huérfana).

**Próximos módulos** (se agregarán sobre esta misma base cuando lo pidas):
Emisión de facturas, Entrega de facturas y Auditoría interna — tal como
aparecen en las hojas restantes de tu Excel.

### Subida de documentos PDF

- Cada Gestión, Proforma y Orden de Compra tiene su propio campo de subida.
- Se valida que el archivo sea realmente un PDF (no solo por la extensión).
- Tamaño máximo: 10 MB (ajustable en `core/Controller.php`, método
  `handleUpload()`). También revisa los límites `upload_max_filesize` y
  `post_max_size` de tu `php.ini` si necesitas subir archivos más grandes.
- Los archivos se guardan con un nombre único (no se puede adivinar la URL
  de otro documento) y la carpeta `public/uploads/` tiene su propio
  `.htaccess` que impide ejecutar cualquier script, solo sirve archivos.

## 6. Dashboard interactivo

El dashboard (`/dashboard`) incluye:

- Tarjetas KPI: total de gestiones, total de proformas, gestiones sin
  asignar a una proforma, y gestiones atrasadas (cotización sin revisar).
- Tarjeta de acceso directo a proformas sin orden de compra generada.
- Gráfico de dona: órdenes de compra por estado (correcta / pendiente / con
  problema).
- Gráfico de barras: monto cotizado por proveedor (top 8).
- Gráfico de líneas: tendencia mensual de gestiones registradas.
- Tabla de las 5 gestiones más atrasadas (más de
  `dias_alerta_gestion` días —por defecto 15— sin revisión de cotización).

Los datos se generan del lado del servidor (PHP) y se pasan a Chart.js como
JSON embebido en la página — no se hace ninguna llamada a APIs externas.

## 7. Migrar los datos del Excel actual

El esquema (`database/schema.sql`) ya refleja las columnas del archivo
`Control de revisión de proformas y facturas.xlsx` (hojas *Gestiones*,
*Proformas*, *OC*, *Emisión de facturas*, *Entrega de facturas*, *Fin del
proceso*). Si quieres que te prepare un script de importación que lea ese
Excel y genere los `INSERT` para la tabla `solicitudes`, dímelo y lo agrego
como un paso adicional.

## 8. Seguridad incluida

- Contraseñas con `password_hash()` / `password_verify()` (bcrypt).
- Protección CSRF en todos los formularios POST.
- Sesiones con cookies `HttpOnly` y `SameSite=Lax`.
- Consultas preparadas (PDO) en todos los modelos — sin concatenación de SQL.
- Control de acceso por rol a nivel de ruta (`config/routes.php`).
- Cabeceras `X-Content-Type-Options`, `X-Frame-Options` vía `.htaccess`.

Antes de salir a producción real, además:
- Cambia `'debug' => false` en `config/config.php`.
- Sirve el sitio por HTTPS y activa `'secure' => true` en las cookies de
  sesión (`public/index.php`).
- Cambia la contraseña del usuario administrador de ejemplo.
