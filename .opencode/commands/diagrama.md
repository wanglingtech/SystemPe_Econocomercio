---
description: Generar o actualizar un diagrama UML en docs/diagramas
---

Usa el skill `arquitectura-drawio` para generar o actualizar el diagrama solicitado.

Solicitud: $ARGUMENTS

Instrucciones:

1. Si no se indican argumentos, revisa la arquitectura actual del proyecto y
   crea o actualiza el diagrama de componentes general en `docs/diagramas/`.
2. Elige el tipo correcto: componentes, clases, secuencia o ER.
3. Guarda el archivo como `docs/diagramas/AAAA-MM-DD-<slug>-<tipo>.drawio`.
4. Valida el XML, envolviéndolo en `<mxfile><diagram>`.
5. Ábrelo con la herramienta MCP `open_drawio_xml` para verificar que renderiza.
6. Actualiza el índice `docs/diagramas/README.md`.
