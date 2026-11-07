# Diagramas de arquitectura

Carpeta de diagramas UML del proyecto en formato nativo de draw.io (`.drawio`),
con exportaciones listas para ver en PNG, SVG y PDF.

## Índice

| Diagrama | Tipo | Archivos | Fecha |
|---|---|---|---|
| **Componentes de SystemPe** | Componentes | [`2026-11-06-componentes-systempe.drawio`](2026-11-06-componentes-systempe.drawio) · [PNG](2026-11-06-componentes-systempe.drawio.png) · [SVG](2026-11-06-componentes-systempe.drawio.svg) · [PDF](2026-11-06-componentes-systempe.drawio.pdf) | 2026-11-06 |

Descripción: navegador y UI, servidor Laravel multi-tenant, módulos de negocio,
`CoreFacturalo`, MariaDB con las cuatro bases, storage y servicios externos
(SUNAT, API Perú, Mercado Pago, Culqi, SMTP), además de la infraestructura local.

## Cómo visualizar un diagrama

### Opción 1 — Doble clic (draw.io Desktop)

El equipo ya tiene **draw.io Desktop 32.6.10** instalado y la extensión
`.drawio` asociada. Haz doble clic en el archivo `.drawio` y se abre en la app.

Ruta de la aplicación:

```text
%LOCALAPPDATA%\Programs\draw.io\draw.io.exe
```

### Opción 2 — Abrir la imagen directamente

No necesitas draw.io para verlo:

- `2026-11-06-componentes-systempe.drawio.png` — imagen para cualquier visor.
- `2026-11-06-componentes-systempe.drawio.svg` — vectorial, se abre en el navegador.
- `2026-11-06-componentes-systempe.drawio.pdf` — para imprimir o compartir.

### Opción 3 — draw.io web

1. Abre <https://app.diagrams.net/>.
2. **File → Open From → Device** y selecciona el `.drawio`.
3. O arrastra el archivo directamente sobre la página.

### Opción 4 — VS Code / Cursor

Instala la extensión **Draw.io Integration** (`hediet.vscode-drawio`) y abre el
archivo `.drawio` dentro del editor.

## Cómo exportar a imagen

Con draw.io Desktop instalado:

```powershell
$drawio = "$env:LOCALAPPDATA\Programs\draw.io\draw.io.exe"
$src    = "docs\diagramas\2026-11-06-componentes-systempe.drawio"

# PNG con XML embebido (sigue siendo editable)
& $drawio -x -f png -e -b 10 -o "$src.png" $src

# SVG
& $drawio -x -f svg -e -b 10 -o "$src.svg" $src

# PDF
& $drawio -x -f pdf -e -b 10 -o "$src.pdf" $src
```

La opción `-e` incrusta el XML del diagrama dentro del PNG/SVG/PDF, así que el
archivo exportado se puede volver a abrir y editar en draw.io.

## Cómo crear un diagrama nuevo

Desde OpenCode:

```text
/diagrama componentes del módulo de inventario
/diagrama clases del módulo de ventas
/diagrama secuencia de emisión de factura
/diagrama ER de las tablas de hoteles
```

O en lenguaje natural:

```text
Documenta la arquitectura del módulo X con un diagrama de componentes en docs/diagramas
```

El flujo lo aplican `AGENTS.md` y el skill `arquitectura-drawio`:

1. Genera el `.drawio` con nombre `AAAA-MM-DD-<slug>-<tipo>.drawio`.
2. Valida el XML y lo envuelve en `<mxfile><diagram>`.
3. Lo abre con el MCP de draw.io.
4. Exporta PNG/SVG/PDF si se solicita.
5. Actualiza este índice.

## Convenciones

- Nombre: `AAAA-MM-DD-<slug>-<tipo>.drawio`
- Tipos: `componentes`, `clases`, `secuencia`, `er`.
- Un archivo por diagrama y por tipo.
- Etiquetas en español; nombres técnicos en su idioma original.
- XML válido, envuelto en `<mxfile><diagram>...</diagram></mxfile>`.
- Sin comentarios XML dentro del archivo.
- Las exportaciones usan doble extensión: `.drawio.png`, `.drawio.svg`, `.drawio.pdf`.

## Tipos de diagrama

| Tipo | Cuándo usarlo |
|---|---|
| **Componentes** | Vista general de un sistema o módulo: capas, servicios, bases de datos e integraciones. |
| **Clases** | Entidades del dominio, atributos, métodos y relaciones. |
| **Secuencia** | Flujo temporal entre actores, controladores, servicios y sistemas externos. |
| **ER** | Modelo de datos: tablas, columnas y cardinalidades. |

## Validar el XML

```powershell
[xml](Get-Content "docs\diagramas\2026-11-06-componentes-systempe.drawio" -Raw) | Out-Null
```
