# Cómo funciona el sistema (visión completa)

Este documento explica **qué hace** SystemPe, **cómo está organizado** y **qué
ocurre por dentro** cuando alguien entra a un tenant o emite un documento.

---

## 1. Resumen en una frase

SystemPe es una aplicación **Laravel multi‑tenant**: un panel central administra
varias empresas y cada empresa (tenant) tiene su **propio subdominio**, su
**propia base de datos MySQL** y su **propio conjunto de usuarios y módulos**.

---

## 2. Diagrama general

```
                         ┌──────────────────────────────┐
                         │        Navegador             │
                         └──────────────┬───────────────┘
                                        │ HTTP :8080
                         ┌──────────────▼───────────────┐
                         │  public/index.php (Laravel)  │
                         └──────────────┬───────────────┘
                                        │
                    ┌───────────────────▼───────────────────┐
                    │  Middleware hyn/multi-tenant          │
                    │  lee el dominio (Host) y busca        │
                    │  el hostname en la base del sistema   │
                    └───────────────┬───────────────────────┘
                                    │
          ┌─────────────────────────┴─────────────────────────┐
          │                                                   │
  ¿El dominio es un tenant?                          ¿Es el dominio base?
          │                                                   │
 ┌────────▼─────────┐                               ┌─────────▼─────────┐
 │ Configura conexión│                              │ Panel del sistema │
 │ 'tenant' con:     │                              │ base: tenancy     │
 │  BD tenancy_<sub> │                              │ usuarios: admins  │
 │  usuario tenancy_<sub>                            └───────────────────┘
 └────────┬─────────┘
          │
 ┌────────▼─────────────────────────────────────────────────┐
 │  Rutas del tenant (routes/web.php)                       │
 │  Auth con App\Models\Tenant\User                         │
 │  Módulos habilitados en module_user / module_level_user  │
 │  Controladores App\Http\Controllers\Tenant\*             │
 │  Modelos App\Models\Tenant\*  +  Modules\*\Models\*      │
 │  Vistas Blade + componentes Vue                          │
 └──────────────────────────────────────────────────────────┘
```

---

## 3. Los dos “mundos”

| | Sistema central | Tenant |
|---|---|---|
| Dominio | `systempe.test` | `sistemaventa.systempe.test` |
| Base de datos | `tenancy` | `tenancy_sistemaventa` |
| Modelo de usuario | `App\Models\System\User` | `App\Models\Tenant\User` |
| Guard | `admin` | `web` |
| Tabla de usuarios | `tenancy.users` | `tenancy_<sub>.users` |
| Para qué sirve | Crear/administrar clientes, planes y módulos | Operar la empresa: ventas, compras, inventario, etc. |

El sistema central **no** ve los datos de negocio de los tenants; solo guarda
metadatos (qué empresa existe, qué plan tiene, qué módulos, su dominio).

---

## 4. Ciclo de una petición HTTP

Ejemplo: `GET http://sistemaventa.systempe.test:8080/documents/create`

1. Windows resuelve el dominio por `hosts` → `127.0.0.1`.
2. El servidor PHP (puerto 8080) recibe la petición y ejecuta `public/index.php`.
3. Laravel arranca (`bootstrap/app.php`) y carga configuración, rutas y providers.
4. El middleware `Hyn\Tenancy\Middleware\EagerIdentification` lee el `Host`
   (`sistemaventa.systempe.test`) y busca ese FQDN en `tenancy.hostnames`.
5. Si lo encuentra, obtiene el `website` asociado y configura la conexión
   `tenant` con:
   - base de datos: `tenancy_sistemaventa`
   - usuario: `tenancy_sistemaventa`
   - contraseña: `md5(APP_KEY + "." + id_website)`
6. `routes/web.php` detecta que hay un hostname y registra las rutas del tenant.
7. El middleware `auth` valida la sesión contra `App\Models\Tenant\User`.
8. `redirect.module` comprueba que el usuario tenga permiso al módulo de esa URL.
9. Se ejecuta el controlador (por ejemplo `Tenant\DocumentController@create`).
10. El controlador consulta modelos del tenant (conexión `tenant`) y devuelve una
    vista Blade. Blade incluye los assets y componentes Vue.
11. El navegador descarga `app.js`, `vendor.js`, `manifest.js` y `app.css` según
    `public/mix-manifest.json`, y Vue monta los componentes interactivos.

Si el dominio es `systempe.test`, el paso 4 no encuentra hostname y se registran
las rutas del **panel del sistema** (guard `admin`, base `tenancy`).

---

## 5. Multi‑tenant en detalle

### 5.1 Tablas que lo gobiernan (en `tenancy`)

| Tabla | Contenido |
|---|---|
| `websites` | Una fila por tenant. `uuid` = nombre de la base (`tenancy_<subdominio>`). |
| `hostnames` | Dominios asociados a un website (`fqdn` → `website_id`). |
| `clients` | Datos comerciales del cliente: nombre, correo, plan, token, `hostname_id`. |
| `plans` / `plan_documents` | Planes contratados y tipos de documento permitidos. |
| `modules` / `module_levels` | Catálogo global de módulos y sub‑opciones. |

Relación:

```
websites 1 ─── N hostnames
   │
   └── clients (1 por website, normalmente)
```

### 5.2 Nombres y credenciales

- Subdominio: `sistemaventa` → FQDN `sistemaventa.systempe.test`.
- Base: `PREFIX_DATABASE` + `_` + subdominio → `tenancy_sistemaventa`.
- Usuario MySQL: igual que la base.
- Contraseña MySQL: `md5(APP_KEY + "." + id_del_website)`.
- Carpeta de archivos del tenant: `storage/app/tenancy/tenants/tenancy_<sub>/`.

### 5.3 ¿Cómo se crea un tenant?

Desde el panel: **Clientes → Nuevo**. Al guardar, el controlador
`System\ClientController@store`:

1. Crea el `website` y el `hostname`.
2. Hyn crea la base de datos y el usuario MySQL.
3. Se ejecutan las **682 migraciones** de `database/migrations/tenant`.
4. Se ejecuta `TenancyDatabaseSeeder` (plantillas de formato, etc.).
5. Se insertan: empresa, configuración, establecimiento, almacén, series y el
   usuario administrador.
6. Se asignan los módulos y niveles marcados (`module_user`, `module_level_user`).
7. Se crea la fila en `clients` con el plan y el token.

### 5.4 Migraciones: dos mundos separados

| Ruta | Qué migra | Cantidad |
|---|---|---|
| `database/migrations` | Base del sistema (`tenancy`) | 56 |
| `database/migrations/tenant` | Cada base de tenant | 682 |

> El orden importa: el sistema debe estar migrado antes que los tenants, porque
> algunas migraciones de tenant consultan tablas del sistema (por ejemplo
> `modules`).

---

## 6. Estructura del proyecto

| Carpeta | Contenido |
|---|---|
| `app/` | Código principal: controladores, modelos, middleware, servicios. |
| `app/CoreFacturalo/` | Núcleo de facturación electrónica: XML, firmas, envío a SUNAT, PDF. |
| `app/Http/Controllers/System/` | Controladores del panel central. |
| `app/Http/Controllers/Tenant/` | Controladores del tenant. |
| `app/Models/System/` | Modelos de la base `tenancy`. |
| `app/Models/Tenant/` | Modelos de la base del tenant. |
| `modules/` | 28 módulos de negocio (Document, Sale, Purchase, Inventory, Hotel, etc.). |
| `resources/js/` | Componentes Vue 2 y lógica del frontend. |
| `resources/views/` | Vistas Blade (layouts, login, system, tenant, errors). |
| `resources/sass/` | SCSS original (el CSS ya está compilado en `public/css`). |
| `database/migrations/` | Migraciones del sistema. |
| `database/migrations/tenant/` | Migraciones de los tenants. |
| `database/seeds/` | Seeders del sistema y de tenants. |
| `public/` | Document root: `index.php`, assets, imágenes, `storage` (junction). |
| `routes/web.php` | Rutas del sistema **y** de los tenants (según el dominio). |
| `config/tenancy.php` | Configuración de hyn/multi-tenant. |
| `config/database.php` | Conexiones `system` y `tenant`. |
| `storage/logs/` | Logs de Laravel. |
| `storage/app/public/` | Archivos públicos (servidos por `/storage`). |
| `docs/` | Esta documentación y scripts SQL. |

---

## 7. Rutas y middlewares

### 7.1 Un solo `routes/web.php` con dos ramas

```php
$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
    Route::domain($hostname->fqdn)->group(function () {
        Auth::routes([...]);
        // rutas del tenant...
    });
} else {
    Route::domain(config('app.url'))->group(function () {
        // login y rutas del panel del sistema...
    });
}
```

- Si hay hostname identificado → rutas de tenant.
- Si no → rutas del sistema (`systempe.test`).

### 7.2 Middlewares importantes

| Middleware | Qué hace |
|---|---|
| `EagerIdentification` (hyn) | Identifica el tenant por el dominio. |
| `HostnameActions` (hyn) | Redirecciones, HTTPS y modo mantenimiento del dominio. |
| `auth` | Exige sesión iniciada. |
| `redirect.module` | Redirige si el usuario no tiene el módulo de la URL. |
| `redirect.level` | Restringe sub‑opciones (niveles) de un módulo. |
| `locked.tenant` | Bloquea si la configuración del tenant está bloqueada. |
| `LockedAdmin` | Bloquea el panel central si la configuración global lo indica. |
| `tenant.internal.mode` | Bloquea emisión si la empresa está en modo SOAP `03` (Interno). |
| `CheckModule` | Verificación de módulo (registrado pero comentado en el Kernel). |

---

## 8. Autenticación

- **Tenant:** guard `web` → provider `users` → `App\Models\Tenant\User`
  (tabla `users` de la base del tenant). Es el login normal del subdominio.
- **Sistema:** guard `admin` → provider `admins` → `App\Models\System\User`
  (tabla `tenancy.users`). El `System\LoginController` usa `Auth::guard('admin')`.
- Las sesiones se guardan en archivos (`SESSION_DRIVER=file`).
- El login del tenant puede personalizarse (logo, fondo, redes) desde
  **Configuración → Login**.
- Existe la opción `use_login_global` en la configuración: permite que el login
  del sistema autentique también a los tenants.

---

## 9. Módulos y permisos

- Los módulos están implementados como paquetes **nwidart/laravel-modules** en
  `modules/` (28 módulos, todos activos en `module.json`).
- El catálogo funcional vive en `tenancy.modules` (24 registros) y
  `tenancy.module_levels` (84 sub‑opciones).
- Por cada tenant:
  - `module_user`: qué módulos ve cada usuario.
  - `module_level_user`: qué sub‑opciones ve cada usuario.
- `RedirectModule` traduce la URL a un grupo de módulo
  (`documents`, `purchases`, `reports`, `inventory`, etc.) y redirige si no hay
  permiso.
- El menú lateral se construye según esos permisos.

Módulos de negocio disponibles:

```
Account, ApiPeruDev, BusinessTurn, Dashboard, Digemid, Document, DocumentaryProcedure,
Ecommerce, Expense, Finance, FullSuscription, Hotel, Inventory, Item, LevelAccess,
MercadoPago, Offline, Order, Padron, Payment, Pos, Production, Purchase, Report,
Restaurant, Sale, Services, Suscription
```

---

## 10. Funcionalidades principales

| Área | Rutas típicas | Tablas principales | Módulo |
|---|---|---|---|
| Ventas / facturación | `/documents`, `/documents/create` | `documents`, `document_items`, `document_payments`, `document_fee` | Document |
| Notas de venta | `/sale-notes` | `sale_notes`, `sale_note_items` | Sale |
| Cotizaciones | `/quotations` | `quotations`, `quotation_items` | Sale |
| Compras | `/purchases` | `purchases`, `purchase_items`, `purchase_payments` | Purchase |
| Gastos | `/expenses` | `expenses` | Expense |
| Productos | `/items` | `items`, `item_warehouse`, `item_lots`, catálogos `cat_*` | Item |
| Clientes / proveedores | `/persons` | `persons`, `person_addresses` | Person (core) |
| Inventario | `/inventory`, `/warehouses` | `inventory_kardex`, `inventory_transactions`, `warehouses` | Inventory |
| POS / caja | `/pos`, `/cash` | `cash`, `cash_documents`, `cash_transactions` | Pos |
| Finanzas | `/finances` | `global_payments`, `account_payments` | Finance |
| Contabilidad | `/account` | `accounting_ledger`, `accounts` | Account |
| Reportes | `/reports/*` | consultas sobre ventas/compras/inventario | Report |
| Hoteles | `/hotels` | `hotel_rooms`, `hotel_rates`, `hotel_rents`, `hotel_rent_items` | Hotel |
| Trámite documentario | `/documentary-procedure` | `documentary_files`, `documentary_offices`, `documentary_processes` | DocumentaryProcedure |
| Ecommerce | `/ecommerce` | `orders`, `order_forms`, `order_notes` | Ecommerce |
| Usuarios / locales | `/users`, `/establishments` | `users`, `establishments`, `series` | LevelAccess |
| Configuración | `/companies`, `/configurations` | `companies`, `configurations` | Config |

---

## 11. Emisión electrónica (SUNAT)

Cada empresa define su modo en `companies.soap_type_id`:

| Código | Modo | Efecto |
|---|---|---|
| `01` | Demo | Permite emitir en modo prueba. |
| `02` | Producción | Envía a SUNAT producción. |
| `03` | Interno | **Bloquea** la creación de documentos (`InternalModeTenant`). |

Flujo de emisión (resumido):

1. Se crea el documento en `/documents/create` (Vue + API Laravel).
2. Se valida y guarda en `documents` / `document_items`.
3. `app/CoreFacturalo` genera el **XML UBL**, calcula totales e impuestos.
4. Se firma digitalmente con el certificado de la empresa.
5. Se envía a SUNAT mediante los servicios SOAP de `app/CoreFacturalo/WS`.
6. Se guarda la respuesta (CDR) y el estado del documento.
7. Se genera el PDF (dompdf / mpdf) y se puede imprimir o enviar por correo.

Series por tipo de documento (en `series`): `F001` facturas, `B001` boletas,
`FC01/BC01` notas, `FD01/BD01` guías, etc.

---

## 12. Base de datos

### 12.1 Base del sistema (`tenancy`, 20 tablas)

| Tabla | Rol |
|---|---|
| `websites` | Tenants (uno por base de datos). |
| `hostnames` | Dominios de cada tenant. |
| `clients` | Datos del cliente/plan. |
| `plans`, `plan_documents` | Planes y documentos permitidos. |
| `modules`, `module_levels`, `module_level_client` | Catálogo de módulos. |
| `configurations` | Configuración global del panel. |
| `users` | Administradores del sistema. |
| `payment_method_types`, `card_brands`, `client_payments` | Pagos de clientes. |
| `jobs`, `failed_jobs` | Colas. |
| `migrations`, `password_resets`, `history_resources`, `restaurant_partners`, `track_api_peru_services` | Soporte. |

### 12.2 Base de cada tenant (259 tablas)

Se pueden agrupar así:

| Grupo | Ejemplos |
|---|---|
| Seguridad | `users`, `module_user`, `module_level_user` |
| Empresa | `companies`, `configurations`, `establishments`, `series`, `warehouses` |
| Catálogos | `cat_*` (documentos, monedas, unidades, afectaciones, etc.) |
| Ventas | `documents`, `document_items`, `document_payments`, `sale_notes`, `quotations` |
| Compras | `purchases`, `purchase_items`, `purchase_orders`, `purchase_quotations` |
| Inventario | `items`, `inventory_kardex`, `inventory_transactions`, `item_warehouse` |
| Finanzas | `global_payments`, `account_payments`, `banks`, `bank_accounts` |
| Contabilidad | `accounts`, `accounting_ledger` |
| Caja / POS | `cash`, `cash_documents`, `cash_transactions` |
| Hoteles | `hotel_rooms`, `hotel_rates`, `hotel_rents` |
| Trámite documentario | `documentary_files`, `documentary_offices`, `documentary_processes` |
| Ecommerce | `orders`, `order_forms`, `order_notes` |
| Soporte | `migrations`, `failed_jobs`, `jobs`, `password_resets` |

> El listado completo de las 259 tablas está en
> [`docs/sql/README.md`](sql/README.md) y en los scripts `.sql`.

### 12.3 Datos que quedaron de ejemplo

- `tenancy.websites`: 5 registros. Los IDs 1 y 2 (`tenancy_demo`,
  `tenancy_sistemaventas`) no tienen hostname ni base: son restos antiguos.
- `tenancy.hostnames`: 3 (sistemaventa, hotelprueba, econocomercio).
- Cada tenant demo tiene 1 empresa, 1 establecimiento, 11 series, 1 usuario
  administrador y 3 productos (EconoComercio conserva sus datos importados).

---

## 13. Frontend

- Las vistas son **Blade** (`resources/views`).
- La interactividad usa **Vue 2** con **Element UI**.
- `resources/js/app.js` registra los componentes globales.
- Cada módulo aporta sus componentes en `modules/<Modulo>/Resources/assets/js`.
- Laravel Mix genera:
  - `public/js/manifest.js` (runtime de webpack)
  - `public/js/vendor.js` (Vue, Element UI, etc.)
  - `public/js/app.js` (código de la aplicación)
- `public/mix-manifest.json` mapea las rutas lógicas a los archivos con hash.
- Los estilos ya están compilados en `public/css/app.css` y `public/css/auth.css`.
- El build se ejecuta con `npm run assets` (ver documentación de instalación).

---

## 14. Archivos y almacenamiento

| Ruta | Uso |
|---|---|
| `storage/app/public/` | Archivos públicos (logos, skins, imágenes). |
| `public/storage` | Junction que apunta a `storage/app/public`. |
| `storage/app/tenancy/tenants/<db>/` | Archivos privados de cada tenant. |
| `storage/framework/` | Caché, sesiones y vistas compiladas. |
| `storage/logs/` | Logs diarios de Laravel. |

Si subes un archivo desde la aplicación y no se ve, revisa primero la junction
`public/storage` y los permisos de escritura.

---

## 15. Tareas programadas y colas

- `app/Console/Kernel.php` programa:
  - `tenancy:run tenant:run` cada minuto (tareas de cada tenant).
  - `status:server` cada hora (CPU/memoria).
- En Windows se puede crear una tarea programada que ejecute:
  `php artisan schedule:run` cada minuto.
- `QUEUE_CONNECTION=database`: las colas usan las tablas `jobs` / `failed_jobs`.
  Para procesarlas: `php artisan queue:work`.

---

## 16. Flujo completo: crear un cliente nuevo

1. Entras al panel central y vas a **Clientes → Nuevo**.
2. Escribes subdominio, correo, contraseña, plan, tipo SOAP y módulos.
3. El sistema crea el `website`, el `hostname` y la base `tenancy_<sub>`.
4. Corre las 682 migraciones del tenant y el seeder.
5. Inserta empresa, configuración, establecimiento, almacén, series y usuario.
6. Asigna módulos y niveles.
7. Agregas el dominio al archivo `hosts` de Windows:
   `127.0.0.1 <subdominio>.systempe.test`.
8. Ya puedes entrar en `http://<subdominio>.systempe.test:8080/login`.

---

## 17. Flujo completo: emitir un documento

1. Usuario entra a `/documents/create`.
2. Elige tipo de documento (factura, boleta, nota), cliente y productos.
3. Vue envía el formulario a la API del tenant.
4. Laravel valida, calcula IGV/ISC/descuentos y guarda el documento.
5. `CoreFacturalo` genera el XML, lo firma y lo envía a SUNAT (según el modo).
6. Se guarda el CDR y el estado.
7. Se genera el PDF y queda disponible para imprimir o enviar.

---

## 18. Backups y actualizaciones

- **Backup manual:** `mysqldump` de `tenancy` y de cada `tenancy_*`
  (ver [`INSTALACION-DESDE-CERO.md`](INSTALACION-DESDE-CERO.md), sección 17).
- **Backup desde el panel:** menú **Backup** del sistema central.
- **Actualizaciones:** el panel tiene **Actualización** (`auto-update`) para
  ejecutar `git pull`, migraciones y limpieza de caché. Úsalo con precaución:
  haz backup antes.
- **Restauración:** importa los `.sql` de [`docs/sql`](sql/) y recrea los usuarios
  MySQL con `php scripts\crear-usuarios-tenant.php`.

---

## 19. Cosas a tener en cuenta

- No cambies `APP_KEY` en una instalación existente sin recrear los usuarios
  MySQL de los tenants (la contraseña se deriva de ella).
- No borres `public/css/app.css` ni `public/css/auth.css`: el build actual solo
  recompila JavaScript.
- `TENANCY_DATABASE_AUTO_DELETE` está en `false` para evitar borrados
  accidentales de bases de datos al eliminar clientes.
- El servidor PHP embebido es para desarrollo local. Para producción usa
  Apache/Nginx + PHP‑FPM y HTTPS.
- Los logs son la primera fuente de diagnóstico: `storage/logs/laravel-*.log`.
