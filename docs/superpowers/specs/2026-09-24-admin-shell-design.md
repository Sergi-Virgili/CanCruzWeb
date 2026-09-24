# Admin Shell Design

## Goal

Crear un template compartido para el panel administrativo de Masia Can Cruz, con navegación lateral colapsable a la izquierda, una cabecera consistente y comportamiento responsive.

## Design

- El layout administrativo será el componente compartido `x-layouts.app`.
- En escritorio, el sidebar estará a la izquierda y tendrá dos estados: expandido y compacto.
- El botón de colapsar permanecerá junto al logo, dentro del sidebar; no habrá un control flotante a la derecha.
- El estado expandido mostrará logo, texto de navegación, contador de pendientes y cuenta del administrador.
- El estado compacto mostrará logo reducido, iconos y tooltips accesibles.
- La cabecera del contenido mostrará únicamente el título de la sección y su contexto breve.
- En móvil, el sidebar comenzará cerrado y se abrirá como panel lateral con overlay y botón accesible.
- Las vistas de dashboard, reservas y calendario conservarán su contenido y rutas actuales, pero usarán el mismo shell.
- El estado compacto se persistirá en `localStorage` para evitar cambios al navegar entre vistas.
- Se respetarán los colores existentes (`forest`, `clay`, `linen`, `paper`) y las fuentes ya configuradas.

## Accessibility

- El control del sidebar tendrá `aria-label`, `aria-expanded` y `aria-controls`.
- La navegación indicará la página actual con `aria-current="page"`.
- El overlay móvil podrá cerrarse con Escape.
- El foco permanecerá visible y los botones tendrán nombres accesibles.

## Scope

Incluye el layout compartido, estilos administrativos, navegación de Dashboard/Reservas/Calendario y pruebas E2E del shell.

No incluye cambios en el modelo de reservas, rutas, autenticación, permisos ni lógica de negocio.

## Verification

- PHPUnit completo.
- Suite Playwright completa.
- Build de Vite.
- Pint sobre los PHP modificados.
