# Documentación de SystemPe

Índice de toda la documentación disponible.

| Documento | Para qué sirve |
|---|---|
| [Instalación desde cero](INSTALACION-DESDE-CERO.md) | Montar el sistema completo en otra PC Windows: PHP, Composer, MariaDB, `.env`, migraciones, assets, hosts y problemas comunes. |
| [Cuentas y arranque](CUENTAS-Y-ARRANQUE.md) | Usuarios y contraseñas, cómo levantar/detener MariaDB y el servidor, verificación, logs y comandos útiles. |
| [Cómo funciona el sistema](COMO-FUNCIONA-EL-SISTEMA.md) | Arquitectura multi‑tenant, ciclo de una petición, módulos, permisos, emisión SUNAT y estructura de la base de datos. |
| [Tecnologías y versiones](TECNOLOGIAS-Y-VERSIONES.md) | Lenguajes, frameworks, librerías, versiones exactas, extensiones PHP y decisiones técnicas. |
| [Scripts SQL](sql/README.md) | Modelo de datos, generación local de dumps y el inventario de las 259 tablas de un tenant. |
| [Integración draw.io MCP](MCP-DRAWIO.md) | Instalación, configuración en OpenCode, herramientas y flujo de trabajo con el servidor oficial de draw.io. |
| [Diagramas de arquitectura](diagramas/README.md) | Carpeta de diagramas `.drawio`, índice y convenciones para crear nuevos. |
| [Legado Facturador PRO](LEGADO-FACTURADOR.md) | README original del producto base y sus términos y condiciones. |

## Scripts del proyecto

| Archivo | Uso |
|---|---|
| `iniciar-systempe.bat` | Inicia MariaDB (si hace falta) y el servidor en el puerto 8080. |
| `configurar-systempe.ps1` | Agrega dominios al `hosts` y registra MariaDB como servicio (ejecutar como Administrador). |
| `scripts/excluir-defender.ps1` | Agrega exclusiones de Windows Defender para acelerar el proyecto (Administrador). |
| `scripts/generar-dumps.ps1` | Genera los dumps SQL locales en `docs/sql` (no se publican). |
| `scripts/prepare-node-sass.js` | Crea el shim `node-sass` → `sass` (dart-sass). |
| `scripts/fix-mix-manifest.js` | Restaura las entradas CSS en `public/mix-manifest.json`. |
| `scripts/crear-usuarios-tenant.php` | Crea/repara los usuarios MySQL de los tenants. |
| `npm run assets:prod` | Recompila los bundles JS minificados y actualiza el manifiesto. |

## Enlaces rápidos

- Sistema central: <http://systempe.test:8080/login>
- Tenant Sistemaventa: <http://sistemaventa.systempe.test:8080/login>
- Tenant Hotelprueba: <http://hotelprueba.systempe.test:8080/login>
- Tenant EconoComercio: <http://econocomercio.systempe.test:8080/login>
