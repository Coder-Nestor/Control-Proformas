# Control de Proformas y Facturas

Aplicación web en **PHP puro con arquitectura MVC** (sin Composer ni frameworks
externos) para dar seguimiento al proceso completo:

**Gestión → Trabajo → Proforma → Orden de Compra → Emisión de Factura → Entrega de Factura**

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
│   ├── Controllers/     Un controlador por módulo (Gestion, Proforma, OrdenCompra,
│   │                    Factura, EntregaFactura, Proveedor, Area, Usuario, Dashboard...)
│   ├── Models/          Acceso a datos vía PDO (consultas preparadas, sin ORM)
│   └── Views/           Vistas PHP — layout compartido + una carpeta por módulo
├── config/
│   ├── config.php       Credenciales de BD, URL de la app, umbrales de alerta
│   │                    (NO se sube a git — ver config.example.php)
│   └── routes.php       Tabla de rutas -> controlador -> roles permitidos
├── core/                Router, Auth, Database, Controller/Model base, helpers
├── database/
│   ├── schema.sql        Estructura completa (todas las tablas, índices, FKs)
│   └── seed_inicial.sql  Datos base: roles, usuario admin, áreas
├── public/               DOCUMENT ROOT del servidor web
│   ├── index.php         Front controller (único punto de entrada)
│   ├── .htaccess         Reescritura de URLs hacia index.php
│   ├── uploads/           PDFs escaneados, uno por módulo (gestiones/, proformas/,
│   │                      ordenes_compra/, facturas/, entregas/)
│   └── assets/
│       ├── css/app.css   Estilos propios
│       ├── js/app.js     JS propio (sidebar, gráficos del Dashboard)
│       ├── img/logo.png  Logo de la empresa
│       └── vendor/       Bootstrap, Bootstrap Icons y Chart.js AUTOALOJADOS
└── logs/
```

**Importante:** el *document root* de Apache debe apuntar a la carpeta
`public/`, no a la raíz del proyecto. Así el código de `app/`, `core/` y
`config/` (con las credenciales de BD) queda fuera del alcance del navegador.

## 3. Instalación paso a paso

### 3.1. Copiar el proyecto al servidor

- XAMPP (Windows): `C:\xampp\htdocs\proformas-app`
- Apache (Linux): `/var/www/proformas-app`

### 3.2. Crear la base de datos

En phpMyAdmin (o por consola), importa **en este orden**:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed_inicial.sql
```

Esto crea las 11 tablas del sistema y las deja listas con:

- Los 4 roles del sistema (administrador, auditoría, solicitante, lectura).
- Un **usuario administrador**: `admin@empresa.com` / `Admin123!`
- Las 6 áreas base (Logística, Finca, Operaciones, IT, Central, Aud. Prod. Agrícola).

⚠️ **Cambia la contraseña del administrador** apenas inicies sesión (menú
*Usuarios → Editar*).

### 3.3. Configurar la conexión a la base de datos

Copia `config/config.example.php` a `config/config.php` y edítalo con tus
datos reales:

```php
'db' => [
    'host'     => '127.0.0.1',
    'database' => 'control_proformas',
    'username' => 'root',
    'password' => 'tu_password',
],
```

Ajusta también la URL base y los umbrales de alerta:

```php
'app' => [
    'url' => 'http://proformas.local',   // o tu VirtualHost / ruta real
    'dias_alerta_gestion' => 15,          // Gestión/Proforma sin revisar
    'dias_alerta_factura' => 8,           // OC, Factura y Entrega (regla crítica)
],
```

### 3.4. Configurar Apache (VirtualHost recomendado)

```apache
<VirtualHost *:80>
    ServerName proformas.local
    DocumentRoot "C:/xampp/htdocs/proformas-app/public"

    <Directory "C:/xampp/htdocs/proformas-app/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

No olvides agregar `127.0.0.1  proformas.local` a tu archivo `hosts`
(`C:\Windows\System32\drivers\etc\hosts`), y verificar que en
`httpd.conf` la línea `Include conf/extra/httpd-vhosts.conf` no esté
comentada.

Si prefieres no usar VirtualHost, entra directo por
`http://localhost/proformas-app/public/login` — el `.htaccess` ya reescribe
las URLs internamente sin necesitar nada más.

### 3.5. Verificar mod_rewrite

En XAMPP/WAMP normalmente ya viene habilitado. En Linux:
```bash
a2enmod rewrite
service apache2 restart
```

### 3.6. Probar

Abre `http://proformas.local/login`, entra con el administrador, y confirma
que el **Dashboard** cargue con sus tarjetas y gráficos.

## 4. Roles del sistema

| Rol            | Permisos                                                              |
|----------------|-------------------------------------------------------------------------|
| Administrador  | Acceso total: usuarios, proveedores, áreas, eliminar cualquier registro |
| Auditoría      | Crear/editar en todos los módulos, avanzar estados, ver catálogos       |
| Solicitante    | Crear/editar Gestiones y Proformas                                      |
| Solo lectura   | Solo puede ver Dashboard y listados                                     |

Los roles se administran desde *Usuarios* (solo el rol Administrador ve ese
menú) y se definen en la tabla `roles`.

## 5. El modelo del negocio

Refleja la lógica del Excel original, con **6 módulos** encadenados:

### 5.1. Gestión (el punto de partida)

Un trabajo cotizado a un proveedor: proveedor, N° de cotización, fechas de
aprobación/finalización/revisión, solicitado por, aprobado por, y un PDF
escaneado de la cotización física.

Una Gestión puede agrupar **varios Trabajos** (por ejemplo, "instalación de
GPS" y "revisión de batería" bajo la misma cotización). Cada Trabajo tiene su
propia descripción, valor, y — lo importante — **su propia Proforma
asignada**, de forma independiente de los demás trabajos de la misma
Gestión.

Desde la ficha de una Gestión, si un Trabajo ya está guardado, aparece un
enlace **"Crear proforma"** que abre el formulario de Proforma con el
proveedor y la cotización ya precargados.

### 5.2. Proforma (agrupa Trabajos)

Se puede crear de **dos formas**:

- **Con N° de cotización**: buscas y vinculas un Trabajo ya existente
  (búsqueda en vivo por AJAX).
- **Como "Mensualidad"** (sin cotización): escribes el trabajo a mano. El
  sistema crea automáticamente, por detrás, una Gestión (sin cotización) y
  un Trabajo vinculados a esta Proforma — así se mantiene la misma
  estructura de datos aunque el flujo visual sea distinto.

La ficha de detalle de cada Proforma muestra todos los Trabajos que tiene
incluidos (puede ser más de uno).

### 5.3. Orden de Compra — relación 1 a 1 con la Proforma

N° OCE e interna, fecha de envío, estado (Correcta/Pendiente/Con problema).
Solo se puede crear sobre una Proforma que aún no tenga una OC.

### 5.4. Emisión de Factura — relación 1 a 1 con la Orden de Compra

N° de Factura (el número real del proveedor), fecha de entrega, estado.
Solo se puede crear sobre una OC que aún no tenga factura.

### 5.5. Entrega de Factura — relación 1 a 1 con la Factura

Fecha de entrega al dueño del gasto y fecha de solicitud de revisión/orden
de pago. Aquí vive la **regla crítica del proceso**: no deben pasar más de
8 días entre ambas fechas.

### 5.6. Catálogos de apoyo

- **Proveedores** y **Áreas**: se pueden activar/desactivar sin perder el
  historial (los registros ya capturados guardan el nombre como texto, no
  como relación viva).
- **Usuarios**: acceso al sistema por rol.

### Reglas de eliminación

Si eliminas una **Proforma**, los Trabajos que tenía asociados **no se
borran**: quedan "sin asignar" para poder reasignarlos. La OC vinculada
(si existía) sí se elimina en cascada, para no dejar una OC huérfana. El
mismo patrón se repite en cada nivel de la cadena (OC → Factura → Entrega).

### Subida de documentos PDF

Cada uno de los 5 módulos (Gestión, Proforma, OC, Factura, Entrega) tiene su
propio campo de PDF, independiente de los demás. Se valida que el archivo
sea realmente un PDF (no solo por la extensión), con un máximo de 10 MB
(ajustable en `core/Controller.php`, método `handleUpload()` — revisa
también `upload_max_filesize` y `post_max_size` en tu `php.ini`). Los
archivos se guardan con nombre único, y `public/uploads/` tiene su propio
`.htaccess` que impide ejecutar cualquier script, solo sirve archivos.

## 6. Dashboard interactivo

`/dashboard` está organizado para mostrar primero lo que necesita acción:

- **4 tarjetas de alerta**: Gestiones sin proforma, Gestiones atrasadas,
  Proformas sin OC, Facturas sin entrega.
- **2 tarjetas grandes**: Entregas atrasadas (la alerta más crítica, regla
  de los 8 días) y Valor total cotizado.
- **2 gráficos**: Facturas por estado (dona) y Tendencia mensual de
  gestiones (línea).
- **Pestañas de Atrasos**: "Gestiones sin revisar" y "Entregas atrasadas",
  cada una con su tabla y link directo al detalle.
- **Pestañas de Resumen** (reconstruye la hoja "Resumen" del Excel
  original): "Resumen · Proformas" y "Resumen · Facturas", encadenando
  Cotización → Proforma → OCE → Factura, con **paginación** (10 filas por
  página) y un botón **Exportar PDF** que imprime la tabla completa con el
  logo de la empresa, en horizontal, con los encabezados repetidos en cada
  página.

Todos los datos se generan del lado del servidor (PHP) y se pasan a
Chart.js como JSON embebido en la página — no se hace ninguna llamada a
APIs externas.

## 7. Seguridad y buenas prácticas incluidas

- Contraseñas con `password_hash()` / `password_verify()` (bcrypt).
- Protección CSRF en todos los formularios POST.
- Sesiones con cookies `HttpOnly` y `SameSite=Lax`.
- Consultas preparadas (PDO) en todos los modelos — sin concatenación de SQL.
- Control de acceso por rol a nivel de ruta (`config/routes.php`).
- Cabeceras `X-Content-Type-Options`, `X-Frame-Options` vía `.htaccess`.
- **Cache-busting automático** de CSS/JS: la función `asset()` agrega
  `?v=<fecha de modificación>` a cada archivo, así el navegador siempre
  descarga la versión más reciente en cuanto cambia, sin depender de que el
  usuario fuerce Ctrl+F5.

Antes de salir a producción real, además:
- Cambia `'debug' => false` en `config/config.php`.
- Sirve el sitio por HTTPS y activa `'secure' => true` en las cookies de
  sesión (`public/index.php`).
- Cambia la contraseña del usuario administrador de ejemplo.

## 8. Subir el proyecto a GitHub

1. Copia `config/config.example.php` (sin credenciales reales) — ya está
   pensado para subirse al repo; el `config.php` real queda excluido por
   el `.gitignore`.
2. Verifica que exista un `.gitignore` en la raíz excluyendo
   `config/config.php`, los PDFs subidos en `public/uploads/*/`, y los
   logs.
3.
   ```bash
   git init
   git add .
   git commit -m "Sistema completo de Control de Proformas y Facturas"
   git remote add origin https://github.com/tu-usuario/tu-repo.git
   git branch -M main
   git push -u origin main
   ```
4. Confirma en GitHub que `config/config.php` y los PDFs de prueba **no**
   aparezcan en la lista de archivos — solo `config.example.php` y las
   carpetas vacías con su `.gitkeep`.
