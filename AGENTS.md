# SystemPe — Instrucciones del proyecto

## Contexto

- Aplicación Laravel 5.7 multi-tenant (hyn/multi-tenant).
- Sistema central en `systempe.test` (base `tenancy`) y tenants por subdominio
  (`tenancy_sistemaventa`, `tenancy_hotelprueba`, `tenancy_econocomercio`).
- Backend PHP 7.4, MariaDB 10.11, frontend Vue 2 + Element UI.
- Servidor local: `php artisan serve --host=0.0.0.0 --port=8080`.
- No exponer credenciales de `.env` ni hashes de contraseñas.

## Comandos útiles

- `php artisan migrate:status` — estado de las migraciones del sistema.
- `php artisan config:clear` — recargar `.env`.
- `npm run assets` — recompilar JS y regenerar `public/mix-manifest.json`.
- `php scripts\crear-usuarios-tenant.php` — reparar usuarios MySQL de tenants.

## Regla obligatoria: documentar la arquitectura con diagramas

Cada vez que se cree un **módulo nuevo**, un **sistema nuevo** o se cambie la
arquitectura de forma relevante:

1. Generar al menos un diagrama **UML** en `docs/diagramas/`.
   - Sistema/módulo nuevo: diagrama de **componentes**.
   - Lógica de dominio con entidades: diagrama de **clases**.
   - Flujo entre actores/servicios: diagrama de **secuencia**.
   - Modelo de datos: diagrama **ER**.
2. Guardar el archivo con el formato:
   `docs/diagramas/AAAA-MM-DD-<slug>-<tipo>.drawio`
   Ejemplo: `2026-11-06-componentes-systempe.drawio`.
3. Validar el XML y envolverlo en `<mxfile><diagram>...</diagram></mxfile>`
   (necesario para que las herramientas MCP de páginas lo lean).
4. Abrirlo con el MCP de draw.io (`open_drawio_xml`) para verificar que renderiza.
5. Actualizar el índice en `docs/diagramas/README.md` con el nombre, tipo y
   descripción del diagrama.

Usa el skill **`arquitectura-drawio`** para el procedimiento completo y el skill
oficial **`drawio`** para la sintaxis XML/Mermaid.

## Convenciones de diagramas

- Etiquetas en español, nombres técnicos en su idioma original.
- Sin comentarios XML (`<!-- -->`) dentro de los `.drawio`.
- IDs únicos por celda; toda arista debe tener `<mxGeometry relative="1" as="geometry"/>`.
- Incluir leyenda o título con la fecha y el alcance del diagrama.
- Un archivo por diagrama y por tipo; no mezclar componentes y secuencia.

## Convenciones técnicas del proyecto

- Los modelos `App\Models\System\*` usan la conexión `system`; los
  `App\Models\Tenant\*` usan `tenant`.
- Las rutas viven en `routes/web.php` (rama sistema / rama tenant) y en
  `modules/*/Routes/`.
- Los módulos de negocio están en `modules/` (nwidart).
- No borrar `public/css/app.css` ni `public/css/auth.css`: el build solo recompila JS.
- Mantener `public/storage` como junction a `storage/app/public`.
