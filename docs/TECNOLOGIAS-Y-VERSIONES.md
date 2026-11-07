# Tecnologías, frameworks y versiones

Inventario técnico de todo lo que usa SystemPe en esta instalación.

---

## 1. Lenguajes utilizados

| Lenguaje | Dónde se usa |
|---|---|
| **PHP 7.4** | Backend completo: Laravel, controladores, modelos, migraciones, seeders, comandos. |
| **JavaScript (ES5/ES6)** | Frontend Vue 2, componentes, lógica de pantallas, build con webpack. |
| **SQL (MySQL/MariaDB)** | Bases de datos, migraciones, consultas, reportes. |
| **Blade / HTML5** | Plantillas de las vistas del sistema y de los tenants. |
| **CSS / SCSS** | Estilos (Porto/AdminLTE + Element UI). El SCSS original está precompilado. |
| **JSON** | `composer.json`, `package.json`, `mix-manifest.json`, configuración de módulos. |
| **Batch (`.bat`)** | Arranque del servidor en Windows (`iniciar-systempe.bat`). |
| **PowerShell** | Scripts de configuración (`configurar-systempe.ps1`). |
| **Markdown** | Documentación (`docs/`). |

---

## 2. Backend

| Componente | Versión | Notas |
|---|---|---|
| PHP | **7.4.33** (x64, VC15) | Laravel 5.7 no soporta PHP 8. |
| Laravel Framework | **5.7.29** | Framework principal. |
| Composer | **2.11.1** | Gestor de dependencias PHP. |

### 2.1 Paquetes PHP principales

| Paquete | Versión | Para qué |
|---|---|---|
| `laravel/framework` | v5.7.29 | Núcleo del framework. |
| `hyn/multi-tenant` | 5.3.1 | Multi‑tenant por dominio y base de datos. |
| `nwidart/laravel-modules` | 5.1.0 | Módulos de negocio en `modules/`. |
| `barryvdh/laravel-dompdf` | v0.8.7 | Generación de PDF. |
| `mpdf/mpdf` | v8.1.1 | PDF alternativo (tickets, reportes). |
| `dompdf/dompdf` | v0.8.6 | Motor PDF base. |
| `setasign/fpdi` | v2.3.6 | Manipulación de PDF. |
| `maatwebsite/excel` | 3.1.25 | Import/export Excel. |
| `phpoffice/phpspreadsheet` | 1.23.0 | Motor de hojas de cálculo. |
| `intervention/image` | 2.7.2 | Tratamiento de imágenes (logos, productos). |
| `laravelcollective/html` | v5.7.1 | Helpers de formularios HTML. |
| `guzzlehttp/guzzle` | 6.5.8 | Cliente HTTP (APIs externas). |
| `doctrine/dbal` | 2.13.9 | Cambios de esquema avanzados. |
| `barryvdh/laravel-cors` | v0.11.4 | CORS. |
| `fideloper/proxy` | 4.4.1 | Soporte de proxies. |
| `rap2hpoutre/laravel-log-viewer` | v1.7.0 | Visor de logs en el panel. |
| `ifsnop/mysqldump-php` | v2.9 | Backups de base de datos desde PHP. |
| `picqer/php-barcode-generator` | v2.2.3 | Códigos de barras. |
| `mercadopago/dx-php` | 2.0.0 | Pasarela Mercado Pago. |
| `culqi/culqi-php` | v1.3.4 | Pasarela Culqi. |
| `laravel/tinker` | v1.0.10 | Consola interactiva. |
| `nesbot/carbon` | 1.39.1 | Fechas. |
| `league/flysystem` | 1.1.9 | Sistema de archivos. |
| `monolog/monolog` | 1.27.1 | Logs. |
| `symfony/*` | 4.4.x | Componentes base de Laravel. |
| `phpunit/phpunit` | 7.5.20 | Tests (dev). |
| `barryvdh/laravel-debugbar` | v3.2.9 | Depuración (dev). |
| `fzaninotto/faker` | v1.9.2 | Datos de prueba (dev). |

Total de paquetes instalados: **158**.

### 2.2 Extensiones PHP habilitadas

`bcmath`, `calendar`, `ctype`, `curl`, `date`, `dom`, `exif`, `fileinfo`,
`filter`, `gd`, `hash`, `iconv`, `intl`, `json`, `libxml`, `mbstring`, `mysqli`,
`mysqlnd`, `openssl`, `pcre`, `PDO`, `pdo_mysql`, `pdo_sqlite`, `Phar`,
`readline`, `Reflection`, `session`, `SimpleXML`, `soap`, `sockets`, `sodium`,
`SPL`, `sqlite3`, `standard`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter`,
`xsl`, `zip`, `zlib`.

Las críticas para este proyecto: **pdo_mysql**, **mbstring**, **openssl**,
**fileinfo**, **gd**, **intl**, **curl**, **zip**.

---

## 3. Frontend

| Componente | Versión | Notas |
|---|---|---|
| Vue.js | **2.7.16** | Framework reactivo. |
| vue-template-compiler | **2.7.16** | Debe coincidir exactamente con Vue. |
| Vuex | 3.6.2 | Estado global. |
| Element UI | 2.13.2 | Biblioteca de componentes. |
| Axios | 0.18.1 | Peticiones HTTP. |
| jQuery | 3.4.1 | Usado por el tema y plugins. |
| Bootstrap | 4.3.1 | Base del tema Porto. |
| Popper.js | 1.14.7 | Tooltips/dropdowns de Bootstrap. |
| Lodash | 4.17.15 | Utilidades JS. |
| Chart.js | 2.7.3 | Gráficos. |
| Moment.js | 2.24.0 | Fechas. |
| CKEditor 5 | 20.0.0 | Editor de texto enriquecido. |
| vue2-dropzone | 3.6.0 | Subida de archivos. |
| vue-chartjs | 3.4.0 | Gráficos Vue. |
| @riophae/vue-treeselect | 0.4.0 | Selectores jerárquicos. |
| vue-data-tables | 3.4.5 | Tablas de datos. |
| vue-ckeditor5 | 0.5.0 | Integración CKEditor. |

> El frontend no usa un SPA completo: Laravel entrega vistas Blade y Vue monta
> componentes en las zonas interactivas.

---

## 4. Compilación de assets

| Herramienta | Versión |
|---|---|
| Node.js | **24.22.1** |
| npm | **11.19.1** |
| Laravel Mix | 3.0.0 |
| webpack | 3.12.0 |
| vue-loader | 13.7.3 |
| babel-loader | 8.0.5 |
| dart-sass (`sass`) | 1.107.1 |
| cross-env | 5.2.0 |

Notas de compatibilidad:

- webpack 3 usa MD4, que OpenSSL 3 bloquea. Por eso el build corre con
  `NODE_OPTIONS=--openssl-legacy-provider`.
- `sass-loader 6` exige `node-sass`, que no funciona en Node 18+. Se usa un shim
  que redirige a **dart-sass** (`scripts/prepare-node-sass.js`).
- El proyecto compila **solo JavaScript**; el CSS ya está compilado en
  `public/css`.

Comando:

```powershell
npm run assets
```

---

## 5. Base de datos

| Componente | Versión |
|---|---|
| Motor | **MariaDB 10.11.16** (compatible con MySQL) |
| Charset | `utf8mb4` |
| Collation | `utf8mb4_unicode_ci` |
| Motor de tablas | InnoDB |
| Cliente | `mysql.exe` / `mysqldump.exe` de XAMPP |

Bases de datos:

| Base | Contenido | Tablas |
|---|---|---|
| `tenancy` | Sistema central | 20 |
| `tenancy_sistemaventa` | Tenant Sistemaventa | 259 |
| `tenancy_hotelprueba` | Tenant Hotelprueba | 259 |
| `tenancy_econocomercio` | Tenant EconoComercio | 259 |
| `tenancy_old_backup` | Respaldo del sistema anterior | 9 |
| `tenancy_econocomercio_old` | Respaldo de EconoComercio anterior | 14 |

El proyecto usa características específicas de MySQL/MariaDB:
`PDO::MYSQL_ATTR_LOCAL_INFILE`, tipos `JSON`, `FULLTEXT`, etc. **No es portable a
SQLite ni PostgreSQL sin cambios profundos.**

---

## 6. Infraestructura y sistema operativo

| Elemento | Versión / detalle |
|---|---|
| Sistema operativo | Windows 10/11 x64 |
| Servidor web | PHP built‑in server (`php artisan serve`) en `0.0.0.0:8080` |
| MariaDB | XAMPP (`C:\xampp\Resources_xampp\mysql`) |
| Servicio Windows | `MariaDB-SystemPe` (inicio automático) |
| Dominios locales | Archivo `hosts` de Windows |
| OpenCode (entorno del agente) | 2.2.5 |

No se usa Apache ni Nginx en esta instalación. El puerto 8080 se eligió para
evitar conflictos con otros servicios.

---

## 7. Módulos de negocio

28 módulos en `modules/` (nwidart), todos activos:

```
Account            ApiPeruDev         BusinessTurn       Dashboard
Digemid            Document           DocumentaryProcedure
Ecommerce          Expense            Finance            FullSuscription
Hotel              Inventory          Item               LevelAccess
MercadoPago        Offline            Order              Padron
Payment            Pos                Production         Purchase
Report             Restaurant         Sale               Services
Suscription
```

Catálogo funcional en `tenancy.modules` (24 registros):

`documents`, `purchases`, `advanced`, `reports`, `configuration`, `pos`,
`dashboard`, `inventory`, `accounting`, `ecommerce`, `cuenta`, `finance`,
`establishments`, `hotels`, `documentary-procedure`, `items`, `persons`,
`digemid`, `apps`, `suscription_app`, `production_app`, `restaurant_app`,
`full_suscription_app`, `generate_link_app`.

---

## 8. Servicios externos integrados

| Servicio | Uso |
|---|---|
| SUNAT (SOAP) | Emisión de comprobantes electrónicos. |
| API Perú / RUC / DNI | Consulta de documentos de identidad. |
| Mercado Pago | Cobros en línea. |
| Culqi | Cobros con tarjeta. |
| SMTP | Envío de correos (configurable en `.env`). |

---

## 9. Dónde se declaran las versiones

| Archivo | Qué define |
|---|---|
| `composer.json` / `composer.lock` | Versiones exactas de PHP y paquetes del backend. |
| `package.json` / `package-lock.json` | Versiones del frontend y del build. |
| `config/app.php` | Providers y aliases de Laravel. |
| `config/database.php` | Conexiones `system` y `tenant`. |
| `config/tenancy.php` | Comportamiento multi‑tenant. |
| `.env` | Entorno, credenciales y drivers. |
| `modules/*/module.json` | Estado de cada módulo. |

---

## 10. Comandos para comprobar versiones

```powershell
php -v
php -m
composer --version
node -v
npm -v
& "C:\xampp\Resources_xampp\mysql\bin\mysql.exe" -u root --skip-password -e "SELECT VERSION();"
php artisan --version
npm ls vue vue-template-compiler --depth=0
```

---

## 11. Restricciones y decisiones técnicas

| Decisión | Motivo |
|---|---|
| PHP 7.4 (no 8) | Laravel 5.7 y dependencias antiguas no son compatibles con PHP 8. |
| MariaDB/MySQL (no SQLite) | Multi‑tenant por base de datos y uso de `LOCAL INFILE`, JSON y FULLTEXT. |
| Build solo JS | El SCSS original depende de fuentes no incluidas y `node-sass` no funciona en Node moderno. |
| dart-sass vía shim | `sass-loader 6` requiere `node-sass`, que no soporta Node 18+. |
| Vue y compilador 2.7.16 | Deben coincidir exactamente; si no, el build falla. |
| Sin source maps en el bundle | Evita un `app.js` de 63 MB; queda en ~20 MB. |
| `TENANCY_DATABASE_AUTO_DELETE=false` | Evita borrar bases de datos por error al eliminar un cliente. |
