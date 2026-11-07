---
name: Arquitectura Draw.io
description: Genera y actualiza documentación de arquitectura en archivos .drawio (UML de componentes, clases, secuencia y ER) dentro de docs/diagramas cuando se crea o modifica un módulo o sistema en este proyecto.
---

# Arquitectura Draw.io

Procedimiento para documentar automáticamente la arquitectura de SystemPe con
diagramas UML nativos de draw.io.

## Cuándo usarlo

- Se crea un módulo nuevo en `modules/`.
- Se crea un sistema o servicio nuevo.
- Se modifica la arquitectura: rutas, modelos, bases de datos, integraciones.
- El usuario pide un diagrama de arquitectura, componentes, clases, secuencia o ER.

## Herramientas disponibles

MCP oficial de draw.io (`drawio`):

| Herramienta | Uso |
|---|---|
| `get_drawio_reference` | Referencia completa de XML o Mermaid. |
| `search_shapes` | Buscar formas (UML, AWS, Azure, etc.). |
| `open_drawio_xml` | Abrir un diagrama XML en el editor draw.io. |
| `open_drawio_mermaid` | Abrir un diagrama Mermaid. |
| `list_pages` / `get_page` / `set_page` | Leer o editar páginas de un `.drawio`. |

Skill oficial `drawio`: sintaxis XML/Mermaid, exportaciones y convenciones.

## Flujo obligatorio

1. **Definir alcance y tipo**
   - Módulo/sistema nuevo → componentes.
   - Entidades y relaciones → clases.
   - Flujo entre actores/servicios → secuencia.
   - Tablas y relaciones → ER.

2. **Elegir nombre**

   ```text
   docs/diagramas/AAAA-MM-DD-<slug>-<tipo>.drawio
   ```

   Ejemplos:
   - `2026-11-06-componentes-systempe.drawio`
   - `2026-11-10-clases-modulo-ventas.drawio`
   - `2026-11-10-secuencia-emision-factura.drawio`

3. **Escribir el XML**

   Estructura mínima de un `.drawio`:

   ```xml
   <mxfile host="app.diagrams.net" agent="OpenCode" type="device">
     <diagram id="slug" name="Título del diagrama">
       <mxGraphModel dx="1200" dy="800" grid="1" gridSize="10" page="1" pageWidth="1654" pageHeight="1169">
         <root>
           <mxCell id="0"/>
           <mxCell id="1" parent="0"/>
           <mxCell id="c1" value="Componente" style="shape=component;whiteSpace=wrap;html=1;" vertex="1" parent="1">
             <mxGeometry x="80" y="80" width="200" height="60" as="geometry"/>
           </mxCell>
         </root>
       </mxGraphModel>
     </diagram>
   </mxfile>
   ```

   Reglas:
   - Sin comentarios XML.
   - IDs únicos.
   - Escapar `&amp;`, `&lt;`, `&gt;`, `&quot;` en atributos.
   - Toda arista necesita `<mxGeometry relative="1" as="geometry"/>`.
   - Contenedores: `swimlane;startSize=28;`.
   - Componentes: `shape=component;`.
   - Bases de datos: `shape=cylinder3;`.
   - Actores: `shape=umlActor;`.

4. **Validar y reparar**

   ```powershell
   node <skill-dir>/../drawio/scripts/find-shapes.js "uml component"   # opcional
   ```

   Validar XML:

   ```powershell
   [xml](Get-Content "docs\diagramas\ARCHIVO.drawio" -Raw) | Out-Null
   ```

5. **Abrir con el MCP**

   Usar `get_page` para obtener el `mxGraphModel` y `open_drawio_xml` para
   previsualizarlo. Confirmar que renderiza sin errores.

6. **Actualizar el índice**

   Agregar la fila en `docs/diagramas/README.md` con:
   nombre, tipo, alcance y fecha.

## Plantillas

### Componentes

Contenedores por capa: cliente, aplicación, datos, servicios externos.
Conectar con aristas etiquetadas (`HTTP`, `consulta`, `envía`, `guarda`).

### Clases

Una caja por clase con compartimentos:

```text
Nombre
--------
atributos
--------
métodos
```

Usar `shape=umlClass` o `swimlane` con tres filas. Relaciones:
- Herencia: `endArrow=block;endFill=0;`.
- Composición: `startArrow=diamondThin;startFill=1;`.
- Asociación: `endArrow=open;`.
- Dependencia: `dashed=1;endArrow=open;`.

### Secuencia

Participantes como `umlLifeline` o cajas superiores; mensajes con flechas
horizontales numeradas. Alternativa: Mermaid `sequenceDiagram` con
`open_drawio_mermaid`.

### ER

Entidades como `shape=table` o `swimlane`; relaciones con cardinalidad
(`1`, `N`) en las etiquetas.

## Checklist antes de terminar

- [ ] Archivo creado en `docs/diagramas/` con el nombre correcto.
- [ ] XML válido y envuelto en `<mxfile><diagram>`.
- [ ] Diagrama abierto con `open_drawio_xml` sin errores.
- [ ] Índice `docs/diagramas/README.md` actualizado.
- [ ] Etiquetas en español y alcance indicado en el título.
