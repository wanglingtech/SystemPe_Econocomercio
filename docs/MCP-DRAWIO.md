# Integración de draw.io MCP en OpenCode

Guía de la integración instalada en este equipo para generar, leer y documentar
diagramas UML de forma automatizada.

---

## 1. Qué se instaló

| Componente | Detalle |
|---|---|
| Paquete | [`@drawio/mcp`](https://www.npmjs.com/package/@drawio/mcp) v1.10.1 (oficial de draw.io Ltd) |
| Tipo | MCP Tool Server (stdio) |
| Instalación | Global: `npm install -g @drawio/mcp` |
| Binario | `drawio-mcp` |
| Configuración | `~/.config/opencode/opencode.jsonc` |
| Servidor | `drawio` (conectado) |
| Visor / exportador | draw.io Desktop **32.6.10** (`%LOCALAPPDATA%\Programs\draw.io\draw.io.exe`) |
| Skill oficial | `~/.config/opencode/skills/drawio/` |
| Skill del proyecto | `.opencode/skills/arquitectura-drawio/SKILL.md` |
| Comando | `/diagrama` (`.opencode/commands/diagrama.md`) |
| Diagramas | `docs/diagramas/` |

### ¿Por qué el Tool Server y no Docker?

- El **MCP Tool Server** (`@drawio/mcp`) abre el diagrama en el editor draw.io
  del navegador y permite leer/editar páginas de archivos `.drawio` locales. Es
  el recomendado para OpenCode.
- La **imagen Docker** `jgraph/drawio-mcp` corresponde al **MCP App Server**, que
  renderiza diagramas inline en clientes con soporte de MCP Apps (Claude, Cursor,
  VS Code). OpenCode no muestra diagramas inline, así que no aporta ventajas aquí.

---

## 2. Instalación

```powershell
npm install -g @drawio/mcp
drawio-mcp --version
```

Alternativa sin instalación global:

```powershell
npx -y @drawio/mcp
```

> En Windows conviene el binario global: con `npx` cada arranque resuelve el
> paquete y puede tardar más que el timeout de inicio del MCP.

---

## 3. Configuración en OpenCode V2

OpenCode V2 usa `mcp.servers` (no `mcpServers`). Bloque añadido a
`~/.config/opencode/opencode.jsonc`:

```jsonc
{
  "$schema": "https://opencode.ai/config.json",
  "mcp": {
    "servers": {
      "drawio": {
        "type": "local",
        "command": ["cmd.exe", "/d", "/c", "drawio-mcp"],
        "timeout": {
          "startup": 120000,
          "catalog": 120000,
          "execution": 600000
        }
      }
    }
  }
}
```

Notas:

- `cmd.exe /d /c` es necesario en Windows para ejecutar el `.cmd` global.
- `timeout.startup` alto por si el primer arranque es lento.
- No se guardan secretos: el servidor es local y no requiere API key.

### Formato genérico `mcpServers`

Otros clientes (Claude Desktop, Cursor, etc.) usan `mcpServers`:

```json
{
  "mcpServers": {
    "drawio": {
      "command": "npx",
      "args": ["-y", "@drawio/mcp"]
    }
  }
}
```

Ese bloque **no** es el de OpenCode V2.

---

## 4. Verificación

```powershell
opencode mcp list
```

Resultado esperado:

```text
✓ drawio      connected
✓ playwright  connected
✓ rea         connected
```

Si `drawio` aparece desconectado:

1. `drawio-mcp --version` debe responder `1.10.1`.
2. Revisa `~/.config/opencode/opencode.jsonc`.
3. Ejecuta `opencode service restart` y vuelve a listar.

---

## 5. Herramientas del servidor

| Herramienta | Para qué |
|---|---|
| `open_drawio_xml` | Abre un diagrama XML en el editor draw.io. |
| `open_drawio_mermaid` | Convierte y abre un diagrama Mermaid. |
| `open_drawio_csv` | Convierte CSV en diagrama (organigramas, etc.). |
| `get_drawio_reference` | Devuelve la referencia XML o Mermaid. |
| `search_shapes` | Busca formas (UML, AWS, Azure, red, etc.). |
| `list_pages` | Lista las páginas de un `.drawio` local. |
| `get_page` | Lee una página concreta. |
| `set_page` | Reemplaza una página concreta. |

> `list_pages`, `get_page` y `set_page` necesitan que el archivo tenga el formato
> `<mxfile><diagram>...</diagram></mxfile>`.

---

## 6. Flujo de trabajo automático

1. Se crea o modifica un módulo/sistema.
2. El agente aplica la regla de `AGENTS.md` y el skill `arquitectura-drawio`.
3. Genera el `.drawio` en `docs/diagramas/` con nombre
   `AAAA-MM-DD-<slug>-<tipo>.drawio`.
4. Valida el XML y lo envuelve en `<mxfile><diagram>`.
5. Lo abre con `open_drawio_xml` para comprobar que renderiza.
6. Actualiza el índice `docs/diagramas/README.md`.

También puedes invocarlo manualmente:

```text
/diagrama componentes del módulo de inventario
/diagrama clases del módulo de ventas
/diagrama secuencia de emisión de factura
/diagrama ER de las tablas de hoteles
```

---

## 7. Comandos de terminal para empezar

```powershell
# 1. Verificar Node y el paquete
node -v
drawio-mcp --version

# 2. Verificar la conexión MCP
opencode mcp list

# 3. Ir al proyecto
cd C:\Users\User\Downloads\systempe

# 4. Ver la carpeta de diagramas
Get-ChildItem .\docs\diagramas

# 5. Abrir el diagrama de componentes con el MCP (desde OpenCode)
#    /diagrama componentes de SystemPe
```

---

## 8. Solución de problemas

| Problema | Causa | Solución |
|---|---|---|
| `drawio` no aparece en `opencode mcp list` | Config mal escrita o binario ausente | Revisar `mcp.servers.drawio`, reinstalar el paquete. |
| El servidor no conecta en Windows | `command` apunta a `npx` o a un `.ps1` | Usar `["cmd.exe","/d","/c","drawio-mcp"]`. |
| `list_pages` devuelve 0 páginas | El `.drawio` es un `mxGraphModel` suelto | Envolverlo en `<mxfile><diagram>`. |
| El navegador no abre | Entorno sin navegador/SSH | Definir `DRAWIO_BROWSER=off` en `environment` y usar la URL devuelta. |
| El diagrama no renderiza | XML mal formado | Validar con `[xml](Get-Content ...)`; sin comentarios XML. |
| Primer arranque lento | `npx` resolviendo paquete | Instalar globalmente. |

---

## 9. Privacidad

El MCP Tool Server **no envía el diagrama a ningún servidor**: lo comprime en el
fragmento `#` de la URL, que el navegador no transmite. Solo se descarga el código
del editor desde `app.diagrams.net` (o la instancia configurada en
`DRAWIO_BASE_URL`).

---

## 10. Visualizar y exportar los diagramas

### Abrir el `.drawio`

El equipo tiene **draw.io Desktop 32.6.10** instalado y la extensión `.drawio`
asociada. Basta con hacer doble clic en el archivo:

```text
docs\diagramas\2026-11-06-componentes-systempe.drawio
```

También puedes abrirlo desde una terminal:

```powershell
Start-Process "docs\diagramas\2026-11-06-componentes-systempe.drawio"
```

O desde el menú de draw.io Desktop: **File → Open**.

### Ver la imagen sin abrir draw.io

En la misma carpeta están las exportaciones:

| Archivo | Uso |
|---|---|
| `2026-11-06-componentes-systempe.drawio.png` | Imagen para cualquier visor. |
| `2026-11-06-componentes-systempe.drawio.svg` | Vectorial, se abre en el navegador. |
| `2026-11-06-componentes-systempe.drawio.pdf` | Imprimir/compartir. |

### Exportar de nuevo

```powershell
$drawio = "$env:LOCALAPPDATA\Programs\draw.io\draw.io.exe"
$src    = "docs\diagramas\2026-11-06-componentes-systempe.drawio"

& $drawio -x -f png -e -b 10 -o "$src.png" $src
& $drawio -x -f svg -e -b 10 -o "$src.svg" $src
& $drawio -x -f pdf -e -b 10 -o "$src.pdf" $src
```

`-e` incrusta el XML en el archivo exportado, por lo que sigue siendo editable.

### Verlo online

1. Abre <https://app.diagrams.net/>.
2. **File → Open From → Device** y selecciona el `.drawio`.
3. O arrastra el archivo sobre la página.

### VS Code / Cursor

Instala la extensión **Draw.io Integration** (`hediet.vscode-drawio`) y abre el
`.drawio` directamente en el editor.
