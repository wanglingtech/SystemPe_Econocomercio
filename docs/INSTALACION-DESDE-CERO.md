# SystemPe — Instalación desde cero (Windows)

Guía para dejar funcionando el sistema **SystemPe** (Laravel 5.7 + multi‑tenant)
en una PC con Windows, partiendo de cero.

> Esta guía está escrita a partir de una instalación real y verificada.
> Si algo no coincide con tu versión de Windows/PHP, usa las rutas de tu equipo.

---

## 1. Qué es el proyecto

SystemPe es un sistema de facturación electrónica multi‑empresa:

- **Panel central (sistema):** se abre en `systempe.test`. Usa la base de datos `tenancy`
  y administra clientes, planes, módulos y dominios.
- **Tenants (empresas):** cada cliente tiene su **propia base de datos**
  (`tenancy_<subdominio>`) y su **propio usuario MySQL** (`tenancy_<subdominio>`).
  Se accede por subdominio, por ejemplo `sistemaventa.systempe.test`.
- **Backend:** Laravel 5.7 + paquete `hyn/multi-tenant`.
- **Frontend:** Vue 2 + Element UI, compilado con Laravel Mix 3 / Webpack 3.

Cada tenant tiene 682 migraciones y el sistema central 56 migraciones. No son
intercambiables: las migraciones del sistema van en `database/migrations` y las
de los tenants en `database/migrations/tenant`.

---

## 2. Versiones con las que se probó

| Componente | Versión |
|---|---|
| Windows | 10 / 11 x64 |
| PHP | 7.4.33 (x64, VC15) |
| MariaDB | 10.11.16 (XAMPP) |
| Composer | 2.11.1 |
| Node.js | 24.22.1 |
| npm | 11.19.1 |
| Laravel | 5.7.29 |
| Vue | 2.7.16 |
| vue-template-compiler | 2.7.16 (debe coincidir con Vue) |
| laravel-mix | 3.0.0 |
| webpack | 3.12.0 |

> **Importante:** con Node 18 o superior, webpack 3 y `node-sass` fallan.
> El proyecto ya trae la solución automatizada (ver paso 8).

---

## 3. Requisitos

- Windows 10/11 de 64 bits.
- PHP 7.4 (no uses PHP 8: Laravel 5.7 y varias dependencias no son compatibles).
- MariaDB 10.x o MySQL 5.7/8 (la app usa funciones específicas de MySQL).
- Composer 2.
- Node.js 18 o superior + npm.
- Permisos de administrador solo para: editar `hosts` y registrar el servicio de MariaDB.

Rutas de ejemplo usadas en esta guía:

```
C:\systempe                                  <- código del proyecto
C:\php74                                     <- PHP 7.4
C:\xampp\Resources_xampp\mysql               <- MariaDB
C:\Users\TU_USUARIO\AppData\Local\Composer   <- Composer
```

---

## 4. Instalar PHP 7.4

1. Descarga PHP 7.4 (x64, VC15) desde <https://windows.php.net/download/>.
2. Descomprime en `C:\php74`.
3. Copia `php.ini-development` como `php.ini`.
4. Edita `php.ini` y habilita:

```ini
extension_dir = "ext"

extension=curl
extension=fileinfo
extension=gd2
extension=intl
extension=mbstring
extension=exif
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sockets
extension=sqlite3
extension=zip
```

5. Agrega `C:\php74` al `PATH` del usuario.
6. Verifica en una terminal nueva:

```powershell
php -v
php -m
php --ini
```

Deben aparecer, como mínimo: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`,
`curl`, `gd`, `intl`, `zip`.

> `php --ini` debe mostrar el `php.ini` que editaste. Si no lo muestra,
> el archivo está en otra carpeta o el `PATH` apunta a otro PHP.

---

## 5. Instalar Composer

```powershell
$dir = "$env:LOCALAPPDATA\Composer"
New-Item -ItemType Directory -Path $dir -Force | Out-Null
Invoke-WebRequest https://getcomposer.org/composer-stable.phar -OutFile "$dir\composer.phar"
```

Crea `C:\Users\TU_USUARIO\AppData\Local\Composer\composer.cmd` con este contenido
(ajusta la ruta de PHP):

```bat
@echo off
"C:\php74\php.exe" "%~dp0composer.phar" %*
```

Agrega `%LOCALAPPDATA%\Composer` al `PATH` del usuario y verifica:

```powershell
composer --version
```

---

## 6. Instalar / iniciar MariaDB

Si usas XAMPP, MariaDB ya viene en `C:\xampp\Resources_xampp\mysql`.

Inicio manual:

```powershell
& "C:\xampp\Resources_xampp\mysql\bin\mysqld.exe" `
  --defaults-file="C:\xampp\Resources_xampp\mysql\bin\my.ini" --console
```

Verifica el puerto 3306:

```powershell
Test-NetConnection 127.0.0.1 -Port 3306
```

### Registrar MariaDB como servicio (recomendado)

En una PowerShell **como Administrador**:

```powershell
$mysqld = "C:\xampp\Resources_xampp\mysql\bin\mysqld.exe"
$ini    = "C:\xampp\Resources_xampp\mysql\bin\my.ini"

& $mysqld --install MariaDB-SystemPe "--defaults-file=$ini"
Start-Service MariaDB-SystemPe
Set-Service MariaDB-SystemPe -StartupType Automatic
```

Si el puerto 3306 ya está ocupado por una instancia manual, detén esa instancia
antes de iniciar el servicio.

### Crear la base del sistema

Con el cliente de MySQL:

```powershell
& "C:\xampp\Resources_xampp\mysql\bin\mysql.exe" -u root -p `
  -e "CREATE DATABASE IF NOT EXISTS tenancy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

> Anota el usuario y la contraseña de MySQL. En XAMPP lo normal es `root` sin
> contraseña; para uso local está bien, pero si defines contraseña, colócala en `.env`.

---

## 7. Código y archivo `.env`

1. Copia el proyecto en `C:\systempe`.
2. Crea el `.env`:

```powershell
cd C:\systempe
Copy-Item .env.example .env
php artisan key:generate
```

3. Edita `.env` con estos valores (ejemplo):

```dotenv
APP_NAME=SystemPe
APP_ENV=local
APP_DEBUG=true
APP_URL=http://systempe.test:8080
APP_URL_BASE=systempe.test

DB_CONNECTION=system
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tenancy
DB_USERNAME=root
DB_PASSWORD=

PREFIX_DATABASE=tenancy

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

TENANCY_DATABASE_AUTO_DELETE=false
TENANCY_DATABASE_AUTO_DELETE_USER=false
```

Notas importantes:

- `APP_URL_BASE` es el dominio base de los subdominios. Si usas otro dominio,
  cámbialo aquí y en `hosts`.
- `PREFIX_DATABASE` define el prefijo de las bases de los tenants (`tenancy_`).
- `TENANCY_DATABASE_AUTO_DELETE=true` hace que al eliminar un cliente se
  **borre su base de datos**. Para pruebas se recomienda `false`.
- **Nunca subas `.env` a un repositorio.** Contiene credenciales y la `APP_KEY`.

Cada vez que cambies `.env`:

```powershell
php artisan config:clear
```

---

## 8. Dependencias PHP

```powershell
cd C:\systempe
composer install --no-interaction
composer check-platform-reqs
```

`check-platform-reqs` debe terminar todo en `success`.

Si algo falla por extensiones, revisa el paso 4.

---

## 9. Crear el esquema del sistema

Con la base `tenancy` ya creada:

```powershell
php artisan migrate
php artisan db:seed
```

Esto crea las 20 tablas del sistema y el usuario inicial:

- Usuario: `admin@gmail.com`
- Contraseña: `123456`

> El seeder también crea el plan "Ilimitado" y los tipos de documento.

Verifica:

```powershell
php artisan migrate:status
```

Deben aparecer las 56 migraciones con `Ran? = Yes`.

> **Orden importante:** primero se migra el sistema (`database/migrations`) y
> después los tenants. Varias migraciones de tenant consultan tablas del sistema
> (por ejemplo `modules`).

---

## 10. Enlace de almacenamiento (`public/storage`)

Laravel necesita que `public/storage` apunte a `storage/app/public`.

En Windows, la forma más simple (sin permisos de administrador) es una **junction**:

```powershell
cd C:\systempe
Remove-Item .\public\storage -Force -ErrorAction SilentlyContinue
cmd /c mklink /J public\storage storage\app\public
```

Verifica:

```powershell
Test-Path .\public\storage\skins\light.css
```

Alternativa oficial (puede requerir permisos o modo desarrollador):

```powershell
php artisan storage:link
```

---

## 11. Compilar los assets del frontend

```powershell
cd C:\systempe
npm install --ignore-scripts
npm run assets
```

### ¿Por qué `--ignore-scripts`?

En Node 18+ los paquetes nativos antiguos (`node-sass`, `canvas`) intentan
compilarse y fallan. No son necesarios para generar el bundle:

- `node-sass` se reemplaza por **dart-sass** (`sass`).
- `canvas` solo se usa en el servidor, no en el build.

### ¿Qué hace `npm run assets`?

1. `scripts/prepare-node-sass.js`: crea un shim `node_modules/node-sass` que
   reexporta `sass` (dart-sass). sass-loader 6 lo necesita para compilar los
   bloques `<style lang="scss">` de algunos `.vue`.
2. Ejecuta webpack con `NODE_OPTIONS=--openssl-legacy-provider`
   (webpack 3 usa MD4, que OpenSSL 3 bloquea por defecto).
3. `scripts/fix-mix-manifest.js`: vuelve a agregar `/css/app.css` y `/css/auth.css`
   en `public/mix-manifest.json` (el build solo genera JS).

Resultado esperado:

```
public/js/app.js        (~20 MB en modo desarrollo)
public/js/vendor.js
public/js/manifest.js
public/mix-manifest.json
```

> **No borres `public/css/app.css` ni `public/css/auth.css`.** Ya vienen
> compilados. El SCSS original depende de fuentes (`element-icons.ttf/woff`) que
> no están incluidas, por eso el proyecto compila solo JavaScript.

Si necesitas recompilar CSS en otra PC, tendrías que agregar esas fuentes a
`resources/sass/fonts` y restaurar las líneas `.sass(...)` en `webpack.mix.js`.

### Errores típicos en este paso

| Error | Solución |
|---|---|
| `Node Sass does not yet support your current environment` | Ejecuta `npm run assets` (crea el shim). |
| `error:0308010C:digital envelope routines::unsupported` | Falta `NODE_OPTIONS=--openssl-legacy-provider` (ya está en el script). |
| `Vue packages version mismatch` | `vue` y `vue-template-compiler` deben tener la misma versión exacta. |
| `Module not found: ./fonts/element-icons.ttf` | Ocurre si intentas compilar SCSS. Usa el build solo‑JS. |

---

## 12. Dominios locales (`hosts`)

Edita `C:\Windows\System32\drivers\etc\hosts` con una PowerShell **como Administrador**:

```powershell
$hosts = "C:\Windows\System32\drivers\etc\hosts"
Add-Content $hosts "127.0.0.1 systempe.test"
Add-Content $hosts "127.0.0.1 sistemaventa.systempe.test"
Add-Content $hosts "127.0.0.1 hotelprueba.systempe.test"
Add-Content $hosts "127.0.0.1 econocomercio.systempe.test"
```

> `hosts` no admite comodines (`*.systempe.test`). Cada tenant nuevo necesita
> su propia línea.

Verifica:

```powershell
Resolve-DnsName sistemaventa.systempe.test
```

Debe responder `127.0.0.1`.

También puedes ejecutar el script incluido (como Administrador):

```powershell
powershell -ExecutionPolicy Bypass -File .\configurar-systempe.ps1
```

Ese script agrega los dominios y registra el servicio de MariaDB si falta.

---

## 13. Crear los tenants (clientes)

### Opción recomendada: desde el panel del sistema

1. Abre `http://systempe.test:8080/login`.
2. Entra con `admin@gmail.com` / `123456`.
3. Ve a **Clientes → Nuevo**.
4. Completa:
   - **Subdominio:** por ejemplo `sistemaventa` → crea `sistemaventa.systempe.test`.
   - **Correo y contraseña:** serán las credenciales del administrador del tenant.
   - **Plan:** por ejemplo "Ilimitado".
   - **Tipo SOAP:** en pruebas elige **Demo (01)**. Si eliges **Interno (03)**, el
     sistema bloquea la creación de documentos con un error 403.
   - **Módulos y niveles:** marca los que tendrá el cliente.
5. Guarda.

El sistema automáticamente:

- Crea la base `tenancy_<subdominio>`.
- Crea el usuario MySQL `tenancy_<subdominio>` con su contraseña.
- Ejecuta las **682 migraciones** del tenant.
- Ejecuta el seeder del tenant (`TenancyDatabaseSeeder`).
- Crea empresa, configuración, establecimiento, almacén, series y usuario admin.
- Asigna los módulos seleccionados.

> Para esto, el usuario MySQL configurado en `.env` debe poder crear bases de
> datos y usuarios (`CREATE DATABASE`, `CREATE USER`, `GRANT`). `root` lo puede hacer.

### Si necesitas crearlo o repararlo manualmente

Cada tenant usa como contraseña:

```
md5( APP_KEY + "." + id_del_website )
```

Puedes calcularla con tinker:

```powershell
php artisan tinker
>>> md5(config('app.key').'.6')
```

Luego, en MySQL:

```sql
CREATE DATABASE tenancy_miempresa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tenancy_miempresa'@'localhost' IDENTIFIED BY 'HASH_CALCULADO';
CREATE USER 'tenancy_miempresa'@'127.0.0.1' IDENTIFIED BY 'HASH_CALCULADO';
GRANT ALL PRIVILEGES ON tenancy_miempresa.* TO 'tenancy_miempresa'@'localhost';
GRANT ALL PRIVILEGES ON tenancy_miempresa.* TO 'tenancy_miempresa'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Y ejecuta las migraciones del tenant apuntando a esa base (lo más sencillo es
crear el cliente desde el panel, que hace todo esto automáticamente).

---

## 14. Arrancar el sistema

### Opción A: script incluido

```powershell
.\iniciar-systempe.bat
```

El script:

- Inicia MariaDB si no está corriendo.
- Arranca el servidor en `0.0.0.0:8080`.

### Opción B: manual

```powershell
cd C:\systempe
php artisan serve --host=0.0.0.0 --port=8080
```

### URLs

| Panel | URL |
|---|---|
| Sistema central | <http://systempe.test:8080/login> |
| Tenant de ejemplo | <http://sistemaventa.systempe.test:8080/login> |
| Hotel | <http://hotelprueba.systempe.test:8080/login> |
| EconoComercio | <http://econocomercio.systempe.test:8080/login> |

### Credenciales de esta instalación de ejemplo

| Acceso | Correo | Contraseña |
|---|---|---|
| Sistema central | `admin@gmail.com` | `123456` |
| `sistemaventa` | `admin@sistemaventa.test` | `123456` |
| `hotelprueba` | `admin@hotelprueba.test` | `123456` |
| `econocomercio` | `admin@econocomercio.test` | `123456` |

> En una instalación nueva, las credenciales del tenant son las que escribas al
> crear el cliente desde el panel del sistema.

---

## 15. Scripts incluidos en el proyecto

| Archivo | Para qué sirve |
|---|---|
| `iniciar-systempe.bat` | Inicia MariaDB (si hace falta) y el servidor en el puerto 8080. |
| `configurar-systempe.ps1` | Agrega dominios al `hosts` y registra MariaDB como servicio. Ejecutar como Administrador. |
| `scripts/prepare-node-sass.js` | Crea el shim `node-sass` → `sass` (dart-sass). |
| `scripts/fix-mix-manifest.js` | Restaura las entradas CSS en `public/mix-manifest.json`. |
| `scripts/crear-usuarios-tenant.php` | Crea o repara los usuarios MySQL de todos los tenants. |
| `npm run assets` | Ejecuta los tres pasos del build de JS. |

---

## 16. Problemas comunes y solución

### 500: `Unable to locate Mix file: /css/app.css`

Falta la entrada de CSS en `public/mix-manifest.json`, o el archivo tiene BOM.

```powershell
npm run assets
```

El script escribe el manifiesto en UTF‑8 **sin BOM**. Laravel no puede leer JSON
con BOM y devuelve exactamente ese error.

### 404 en `/storage/...` (por ejemplo `/storage/skins/light.css`)

Falta el enlace de almacenamiento:

```powershell
cmd /c mklink /J public\storage storage\app\public
```

### `Access denied for user 'tenancy_xxx'@'localhost'`

El usuario MySQL del tenant no existe o su contraseña no coincide. Créalo con la
fórmula del paso 13 (`md5(APP_KEY + "." + id_website)`) y otorga permisos sobre
`tenancy_xxx` para `localhost` **y** `127.0.0.1`.

### `Table 'tenancy.modules' doesn't exist` al migrar un tenant

La base del sistema está incompleta. Ejecuta primero:

```powershell
php artisan migrate
php artisan db:seed
```

y vuelve a intentar las migraciones del tenant.

### `Node Sass does not yet support your current environment`

```powershell
npm run assets
```

o manualmente:

```powershell
node scripts/prepare-node-sass.js
```

### `error:0308010C:digital envelope routines::unsupported`

```powershell
$env:NODE_OPTIONS="--openssl-legacy-provider"
npm run development
```

### `Vue packages version mismatch`

```powershell
npm install --save-dev --save-exact vue@2.7.16 vue-template-compiler@2.7.16
```

Ambos paquetes deben tener **exactamente** la misma versión.

### 403 `Acceso denegado: Su cuenta está inactiva` al crear documentos

No es la cuenta: es el middleware `InternalModeTenant`. La empresa está en modo
**Interno (SOAP 03)** y ese modo bloquea la emisión de documentos. Cámbiala a
**Demo (01)** o **Producción (02)**:

```sql
UPDATE tenancy_miempresa.companies SET soap_type_id = '01';
```

Luego recarga con `Ctrl+F5`.

### 419 `Page Expired` al iniciar sesión

La sesión o el token CSRF caducó. Recarga la página de login (`Ctrl+F5`) e
intenta de nuevo. No es un error de instalación.

### 500 `Trying to get property 'id' of non-object` en `/items/export/barcode/last`

El tenant no tiene ningún producto y ese endpoint espera al menos uno. Crea un
producto desde **Productos/Servicios → Nuevo**. Es un comportamiento del código
original, no del entorno.

### El panel tarda en cargar la primera vez

`public/js/app.js` pesa ~20 MB en modo desarrollo. El navegador lo cachea; las
siguientes páginas cargan rápido. Si necesitas optimizar, se puede intentar un
build de producción, pero webpack 3 + Node 24 puede fallar al minificar
dependencias modernas.

---

## 17. Backups

### Respaldar todo

```powershell
$mysqldump = "C:\xampp\Resources_xampp\mysql\bin\mysqldump.exe"
& $mysqldump -u root -p --routines --triggers --single-transaction `
  --databases tenancy tenancy_sistemaventa tenancy_hotelprueba tenancy_econocomercio `
  --result-file="C:\backups\systempe-$(Get-Date -Format yyyyMMdd).sql"
```

### Restaurar

```powershell
& "C:\xampp\Resources_xampp\mysql\bin\mysql.exe" -u root -p < "C:\backups\systempe-20260101.sql"
```

### Bases de respaldo que quedaron en esta PC

| Base | Contenido |
|---|---|
| `tenancy_old_backup` | Estado anterior de la base del sistema (9 tablas). |
| `tenancy_econocomercio_old` | Datos anteriores del tenant EconoComercio. |

No las borres hasta validar que todo funciona.

---

## 18. Lista de verificación final

- [ ] `php -v` → 7.4.x
- [ ] `php -m` → `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `intl`, `zip`
- [ ] `composer check-platform-reqs` → todo `success`
- [ ] `php artisan migrate:status` → 56 migraciones `Yes`
- [ ] `public/storage` es una junction y `public/storage/skins/light.css` existe
- [ ] `npm run assets` termina sin errores
- [ ] `public/mix-manifest.json` contiene `/js/app.js`, `/js/vendor.js`, `/js/manifest.js`, `/css/app.css`, `/css/auth.css`
- [ ] `hosts` tiene `127.0.0.1 systempe.test` y cada subdominio
- [ ] `http://systempe.test:8080/login` responde 200
- [ ] Cada tenant responde 200 en `/login` y el dashboard monta Vue sin errores de consola
- [ ] MariaDB arranca automáticamente (servicio) o con `iniciar-systempe.bat`

---

## 19. Notas finales

- **No cambies a PostgreSQL/SQLite:** el proyecto usa `PDO::MYSQL_ATTR_LOCAL_INFILE`,
  `FULLTEXT`, `JSON` y otras particularidades de MySQL.
- **`.env` es privado.** No lo compartas ni lo subas a git.
- **Cron/queue:** si vas a usar envíos automáticos o colas, configura el
  Programador de tareas de Windows para `php artisan schedule:run` y ejecuta un
  worker de colas (`php artisan queue:work`).
- **Producción:** cambia `APP_ENV=production`, `APP_DEBUG=false`, usa HTTPS y
  contraseñas fuertes. Esta guía es para desarrollo local.

---

## 20. Cambios aplicados a este proyecto

Estos cambios ya están incluidos en la copia del proyecto y son los que permiten
que funcione en Windows con Node moderno:

| Archivo | Cambio |
|---|---|
| `webpack.mix.js` | Build **solo JavaScript** (`mix.js` + `extract`), sin `.sass(...)` y con `devtool: false`. Evita el SCSS roto y bundles gigantes. |
| `package.json` | Script `npm run assets`; `vue` y `vue-template-compiler` fijados en `2.7.16`. |
| `scripts/prepare-node-sass.js` | Shim `node-sass` → `sass` (dart-sass) para sass-loader 6. |
| `scripts/fix-mix-manifest.js` | Restaura `/css/app.css` y `/css/auth.css` en el manifiesto. |
| `resources/js/views/tenant/documents/partials/item.vue` | Se agregó `history_item_id: null` en `data()` para eliminar el warning de Vue. |
| `resources/js/views/system/plans/form.vue` | Se agregaron `quantity: 0` y `toAttend: 0` en `data()` para eliminar warnings de Vue. |
| `public/storage` | Junction a `storage/app/public`. |
| Bases de tenants demo | `companies.soap_type_id = '01'` (modo Demo) para permitir emitir documentos. |
| Bases de datos | Reconstruidas desde migraciones. Respaldos: `tenancy_old_backup`, `tenancy_econocomercio_old`. |
| `iniciar-systempe.bat` | Portable: usa `%~dp0`, PHP del `PATH` y permite `SYSTEMPE_MYSQL_BIN` / `SYSTEMPE_PORT`. |

No se modificó nada dentro de `vendor/` ni se cambiaron las reglas de negocio del
sistema: los ajustes son de entorno, build y dos propiedades de Vue que faltaban.

---

## 21. Documentación relacionada

- [Cuentas y arranque de servicios](CUENTAS-Y-ARRANQUE.md)
- [Cómo funciona el sistema](COMO-FUNCIONA-EL-SISTEMA.md)
- [Tecnologías y versiones](TECNOLOGIAS-Y-VERSIONES.md)
- [Scripts SQL completos](sql/README.md)
- [Índice de documentación](README.md)
