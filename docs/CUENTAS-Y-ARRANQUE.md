# Cuentas y arranque de servicios

Documento operativo: **qué cuentas existen**, **cómo levantar el sistema** y
**cómo verificar que todo esté arriba**.

> **Seguridad:** las cuentas de este documento son de desarrollo/demo. Cambia
> las contraseñas antes de publicar el sistema en internet y nunca subas el
> archivo `.env` al repositorio.

---

## 1. Cuentas de acceso

### 1.1 Sistema central (panel de administración)

Administra clientes, planes, módulos y dominios. No es un tenant.

| Campo | Valor |
|---|---|
| URL | <http://systempe.test:8080/login> |
| Correo | `admin@gmail.com` |
| Contraseña | `123456` |
| Base de datos | `tenancy` |
| Tabla de usuarios | `tenancy.users` |

### 1.2 Tenants (empresas)

Cada tenant tiene su propio dominio, su propia base de datos y su propio usuario
administrador. Las cuentas **no se comparten** entre tenants.

| Tenant | URL | Correo | Contraseña | Base de datos |
|---|---|---|---|---|
| Sistemaventa | <http://sistemaventa.systempe.test:8080/login> | `admin@sistemaventa.test` | `123456` | `tenancy_sistemaventa` |
| Hotelprueba | <http://hotelprueba.systempe.test:8080/login> | `admin@hotelprueba.test` | `123456` | `tenancy_hotelprueba` |
| EconoComercio | <http://econocomercio.systempe.test:8080/login> | `admin@econocomercio.test` | `123456` | `tenancy_econocomercio` |

> En una instalación nueva, las cuentas de los tenants se crean desde el panel
> del sistema (**Clientes → Nuevo**) con el correo y la contraseña que tú elijas.

### 1.3 Usuarios MySQL

| Usuario | Uso | Contraseña |
|---|---|---|
| `root` | Conexión de la aplicación (`.env`) | definida en `.env` (privado) |
| `tenancy_sistemaventa` | Tenant Sistemaventa | derivada de `APP_KEY` |
| `tenancy_hotelprueba` | Tenant Hotelprueba | derivada de `APP_KEY` |
| `tenancy_econocomercio` | Tenant EconoComercio | derivada de `APP_KEY` |

La contraseña de cada usuario de tenant **no se guarda en texto plano** en ningún
archivo: se calcula con:

```
md5( APP_KEY + "." + id_del_website )
```

Si necesitas recrearlas (por ejemplo al restaurar en otra PC con otra `APP_KEY`):

```powershell
php scripts\crear-usuarios-tenant.php
```

### 1.4 ¿Dónde se guardan las contraseñas?

- Las contraseñas de los usuarios de la aplicación se guardan **hasheadas con
  bcrypt** en las tablas `users` (sistema) y `users` (cada tenant).
- Nadie puede leer la contraseña original desde la base de datos.
- La `APP_KEY` y las credenciales de MySQL están en `.env` (privado).

---

## 2. Servicios que deben estar funcionando

| Servicio | Puerto | Para qué |
|---|---|---|
| MariaDB | `3306` | Base de datos de todas las empresas |
| Servidor PHP (Laravel) | `8080` | Aplicación web |

No se necesita Apache. El sistema usa el servidor embebido de PHP
(`php artisan serve`).

---

## 3. Arranque rápido (recomendado)

Doble clic o desde una terminal:

```powershell
cd C:\Users\User\Downloads\systempe
.\iniciar-systempe.bat
```

El script:

1. Comprueba si MariaDB responde en el puerto 3306.
2. Si no responde, inicia `mysqld` con `my.ini`.
3. Arranca Laravel en `0.0.0.0:8080`.

Déjalo abierto mientras uses el sistema. Para detenerlo: `Ctrl+C`.

---

## 4. Arranque manual paso a paso

### 4.1 MariaDB

**Opción A — servicio de Windows (recomendado):**

```powershell
# Ver estado
Get-Service MariaDB-SystemPe

# Iniciar (requiere PowerShell como Administrador)
Start-Service MariaDB-SystemPe

# Detener
Stop-Service MariaDB-SystemPe
```

**Opción B — instancia manual (la que está en uso actualmente):**

```powershell
& "C:\xampp\Resources_xampp\mysql\bin\mysqld.exe" `
  --defaults-file="C:\xampp\Resources_xampp\mysql\bin\my.ini" --console
```

Deja esa ventana abierta. Para detenerla: `Ctrl+C`.

> No ejecutes las dos opciones a la vez: solo una puede usar el puerto 3306.

### 4.2 Servidor PHP

```powershell
cd C:\Users\User\Downloads\systempe
php artisan serve --host=0.0.0.0 --port=8080
```

Para detenerlo: `Ctrl+C`.

---

## 5. Verificar que todo está arriba

```powershell
# Puertos
Test-NetConnection 127.0.0.1 -Port 3306
Test-NetConnection 127.0.0.1 -Port 8080

# Respuestas HTTP (deben ser 200)
curl.exe -s -o NUL -w "%{http_code}`n" http://systempe.test:8080/login
curl.exe -s -o NUL -w "%{http_code}`n" http://sistemaventa.systempe.test:8080/login
```

También puedes abrir en el navegador:

- <http://systempe.test:8080/login>
- <http://sistemaventa.systempe.test:8080/login>

---

## 6. Detener todo

1. En la ventana de Laravel: `Ctrl+C`.
2. MariaDB:
   - Si usas el servicio: `Stop-Service MariaDB-SystemPe` (como Administrador).
   - Si usas la instancia manual: `Ctrl+C` en su ventana, o
     `Get-Process mysqld | Stop-Process`.

---

## 7. Estado actual en esta PC

- El servicio **`MariaDB-SystemPe`** está registrado con inicio **Automático**,
  pero ahora mismo está *Stopped* porque hay una instancia manual usando el
  puerto 3306.
- Al reiniciar la PC, el servicio arrancará solo y no harás nada.
- Si quieres pasar al servicio sin reiniciar:
  1. Detén la instancia manual de `mysqld`.
  2. Abre PowerShell como Administrador.
  3. `Start-Service MariaDB-SystemPe`.

---

## 8. Cambiar contraseñas

### 8.1 Sistema central

Desde la interfaz del sistema no hay pantalla de perfil; usa tinker:

```powershell
cd C:\Users\User\Downloads\systempe
php artisan tinker
```

```php
$u = App\Models\System\User::where('email', 'admin@gmail.com')->first();
$u->password = bcrypt('NUEVA_CLAVE');
$u->save();
```

### 8.2 Tenant

La forma más simple es desde la interfaz del tenant:

1. Inicia sesión como administrador del tenant.
2. Ve a **Usuarios/Locales & Series → Usuarios**.
3. Edita el usuario y cambia la contraseña.

---

## 9. Logs y diagnóstico

| Qué | Dónde |
|---|---|
| Errores de Laravel | `storage\logs\laravel-AAAA-MM-DD.log` |
| Salida de `php artisan serve` | La misma ventana donde lo ejecutaste |
| Error de MariaDB | `C:\xampp\Resources_xampp\mysql\data\*.err` |

Ver los últimos errores:

```powershell
Get-Content .\storage\logs\laravel-$(Get-Date -Format yyyy-MM-dd).log -Tail 50
```

Limpiar cachés después de cambiar `.env` o configuración:

```powershell
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 10. Comandos útiles

| Comando | Para qué |
|---|---|
| `php artisan migrate:status` | Ver si las 56 migraciones del sistema están aplicadas |
| `php artisan config:clear` | Recargar `.env` |
| `php artisan route:clear` | Limpiar rutas cacheadas |
| `php artisan view:clear` | Limpiar vistas compiladas |
| `npm run assets` | Recompilar JS y regenerar el manifiesto |
| `php scripts\crear-usuarios-tenant.php` | Recrear usuarios MySQL de tenants |
| `php artisan tinker` | Consola interactiva de Laravel |

---

## 11. Problemas rápidos

| Síntoma | Causa probable | Solución |
|---|---|---|
| `Connection refused` en 3306 | MariaDB no está corriendo | Inicia el servicio o `mysqld` |
| `Port 8080 already in use` | Ya hay un servidor PHP | Cierra la otra ventana o usa otro puerto |
| `Access denied for user 'tenancy_...'` | Usuario MySQL del tenant desactualizado | `php scripts\crear-usuarios-tenant.php` |
| `419 Page Expired` | Sesión/CSRF vencido | Recarga el login con `Ctrl+F5` |
| `Unable to locate Mix file /css/app.css` | Manifiesto incompleto | `npm run assets` |
| 404 en `/storage/...` | Falta la junction | `cmd /c mklink /J public\storage storage\app\public` |
| 403 al crear documentos | Empresa en modo SOAP 03 | Cambiar a `01` (ver documentación de instalación) |
