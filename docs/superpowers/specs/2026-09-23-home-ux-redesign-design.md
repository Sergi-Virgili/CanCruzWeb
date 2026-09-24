# Home UX/UI Redesign Design

## Status

Approved for implementation planning on 2026-09-23.

## Context

The current home page combines a large photographic hero, a floating reservation form, long generic content blocks and a sidebar menu. The reservation flow works, but the page does not clearly communicate that Masia Can Cruz is a complete rural house for families and groups. Its navy-and-gold visual language feels closer to a generic hotel than to a contemporary rural home, desktop layouts stretch too widely, and the form asks for personal details before the guest has understood availability.

The redesign must make the property feel honest, warm and rooted in the Montseny while keeping the interface current. Reservation remains the primary conversion goal.

## Goals

- Present Masia Can Cruz as a complete house for families and groups of up to eight people.
- Communicate a contemporary rustic identity through restrained typography, natural colors, real photography and clear spatial limits.
- Make availability the first interaction through one range calendar with visible `START` and `END` states.
- Move contact details after date selection so the guest completes one task at a time.
- Create a coherent responsive system rather than a desktop layout compressed onto mobile.
- Keep all existing reservation, availability and server-validation behavior intact.

## Non-Goals

- Adding online payment, instant confirmation or pricing.
- Adding room-by-room booking; the product is the complete house.
- Inventing amenities, reviews, property spaces or guest claims.
- Reworking the authenticated administration interface.
- Changing reservation statuses or availability domain rules.

## Audience And Positioning

The primary audience is families and groups seeking a complete rural house near Barcelona. The main promise is shared time in a private, characterful place rather than hotel-style luxury.

The first viewport must establish four facts:

1. Masia Can Cruz is in the Parc Natural del Montseny.
2. The guest reserves the complete house.
3. The house accommodates up to eight people.
4. Booking is direct.

## Visual Direction

The approved direction is **contemporary rustic**.

- **Palette:** forest green, clay, linen, warm stone and off-white. Navy and hotel-style gold are removed from the public home.
- **Typography:** an editorial serif for headings paired with a restrained sans-serif for navigation, controls and body copy.
- **Photography:** large, natural, minimally processed and used as evidence of the property rather than decoration.
- **Shape language:** soft but restrained radii, asymmetric image crops inspired by arches and stone openings, thin borders and low-contrast shadows.
- **Density:** generous but bounded spacing. The page must not stretch indefinitely on wide monitors.
- **Motion:** subtle reveal and hover transitions only; no autoplay carousel, parallax or decorative animation that obstructs booking.

## Desktop Layout Limits

- Page frame: maximum `1280px` for the designed surface.
- Primary content grid: maximum `1120px`.
- Booking calendar module: approximately `960px` to `1040px` maximum.
- Editorial text measure: `520px` to `680px` depending on context.
- Full-bleed color and image backgrounds may reach the viewport edges, but their content remains aligned to the primary grid.
- The hero should be visually strong without consuming an entire desktop screen; target height is approximately `560px` including its transition into the booking module.

## Information Architecture

### 1. Header

A contained header with the wordmark, anchor navigation and a persistent `Reservar` action. On desktop it remains inside the content grid. On mobile it becomes a compact header with a menu button and a direct booking action.

Navigation targets:

- La casa
- Vivir Can Cruz
- Montseny
- Información
- Contacto

### 2. Hero

The hero uses a real exterior or atmosphere photograph and concise copy. It includes the positioning statement and the three product facts: complete house, up to eight people and direct booking.

Proposed narrative direction:

- Kicker: `Casa completa · Parc Natural del Montseny`
- Main statement: `Una casa con raíces. Un lugar para estar juntos.`
- Support: a short sentence about families, groups, nature and proximity to Barcelona.

Final marketing copy may be refined during implementation, but it must stay factual and concise.

### 3. Availability And Reservation

Availability is the first task. The guest sees one calendar range selector, not two competing calendar widgets.

The range interface contains:

- A `START` state for arrival.
- An `END` state for departure.
- One visible month on mobile and one or two months on desktop depending on available width.
- Clearly disabled occupied nights.
- A selected range treatment.
- Number of nights.
- Previous and next month controls.
- A `Continuar reserva` action enabled only after a valid range is selected.

After the range is selected, the guest advances to the contact fields:

- Nombre completo
- Correo electrónico
- Mensaje
- Submit action

The date values remain normal `entry_date` and `out_date` form fields for the existing Laravel endpoint. JavaScript enhances the interaction; without JavaScript, native date inputs and server validation remain available.

The endpoint and domain behavior remain unchanged:

- Only confirmed reservations occupy dates.
- Occupancy is `[entry_date, out_date)`.
- The checkout date is available for another arrival.
- Server validation is authoritative even if availability cannot be fetched.

### 4. The House

An editorial split section introduces the house as a complete private space. It explains shared areas, character and how groups use the house. It must not describe the property as a collection of hotel suites.

### 5. Product Pillars

Three concise pillars summarize the stay:

- Casa completa
- Piscina y jardín
- Bienestar

Each pillar uses factual copy and links to supporting photography or details.

### 6. Living Can Cruz

A short narrative sequence shows a possible day at the house: slow morning, garden and pool, nearby Montseny and an evening together. This section communicates use and atmosphere without promising services that do not exist.

### 7. Gallery

The gallery uses real property photography for the house, bedrooms, shared spaces, garden, pool and wellness area. Existing real images may be retained where quality is sufficient.

Image policy:

- Property-specific claims use only real property photography.
- Stock photography may support generic Montseny landscape or detail imagery when clearly contextual.
- Generated imagery may be used during design as a replaceable placeholder, never published as a representation of a real Can Cruz space.
- Missing pool, garden or shared-space photography is shown as a content requirement, not silently replaced with fictional property imagery.

### 8. Practical Information

This section answers booking questions without forcing the guest to search:

- Capacity: up to eight people.
- Complete-house booking model.
- Included amenities.
- Arrival and departure expectations.
- Parking and access.
- How direct reservation requests are confirmed.

Only verified facts are published.

### 9. FAQ And Final CTA

A compact FAQ handles the most common objections. The closing section repeats availability rather than presenting another full form. It scrolls or links back to the booking module while preserving the selected range.

### 10. Footer

The footer contains the wordmark, Montseny location statement, real contact and social links, and legal links. Placeholder `#` links are not retained in the redesigned production page.

## Responsive Behavior

### Desktop

- Content is constrained by the approved maximum widths.
- The hero and booking module share one visual composition.
- The calendar shows one or two months based on available width, never an unbounded panel.
- Sections alternate image and text while retaining a consistent grid.

### Mobile

- Navigation becomes compact and touch-friendly.
- The hero is shorter and prioritizes the main statement and product facts.
- The calendar is full-width and inline, not a small floating popover.
- `START` and `END` remain visible above the calendar.
- One month is shown at a time.
- The primary action occupies the available width.
- Editorial split layouts collapse to one column in a deliberate order: copy, proof image, supporting details.

## Components

The home should be decomposed into focused Blade components or partials where reuse or clarity warrants it:

- Public header and mobile navigation.
- Hero.
- Range calendar and progressive reservation form.
- Section heading.
- Product pillar.
- Gallery.
- FAQ item.
- Public footer.

The calendar JavaScript remains responsible only for availability loading, range selection, presentation state and progressive disclosure. Server validation and reservation persistence remain in Laravel.

## Interaction And Error States

- While availability loads, the calendar shows a non-blocking loading state.
- If availability fails, the page explains that dates will be checked on submission and retains the native/server-validated fallback.
- Occupied nights cannot be selected in the enhanced calendar.
- Invalid or conflicting submissions retain user input and return focus to the relevant booking section.
- Success and warning messages are visually integrated near the booking flow and announced accessibly.
- Menu and FAQ controls expose correct expanded state and support keyboard interaction.
- Motion respects `prefers-reduced-motion`.

## Accessibility

- Semantic landmarks and heading hierarchy.
- Keyboard-operable menu, calendar flow, FAQ and form.
- Visible focus states with sufficient contrast.
- Labels remain associated with form controls.
- Status and error messages use appropriate live regions.
- Decorative images use empty alternative text; informative property images use concise descriptions.
- Touch targets meet a minimum comfortable size.

## SEO And Performance

- The home retains server-rendered primary content.
- Title and metadata describe a complete rural house in the Montseny.
- Images use explicit dimensions, responsive sources and lazy loading below the fold.
- The hero uses an optimized source suitable for the rendered size.
- No carousel or heavy animation dependency is introduced.
- Structured data may be considered later after all business details are verified; it is not required for the initial redesign.

## Testing

### PHPUnit

- The redesigned home renders its key positioning and reservation component.
- Existing reservation validation and availability tests remain green.
- Session success, warning and validation states render in the booking flow.

### Playwright

- Desktop and mobile viewports render the expected navigation and booking structure.
- A guest selects `START` and `END` through one calendar and proceeds to contact details.
- Occupied nights remain unavailable.
- Same-day turnover remains available.
- The selected range survives the transition to contact details and validation errors.
- Mobile navigation, FAQ and final CTA are operable.
- The no-JavaScript submission path remains covered by server validation.

### Visual QA

- Desktop widths include common laptop and wide-monitor sizes.
- Mobile widths include narrow and standard phone viewports.
- No horizontal overflow.
- Real-property images remain correctly cropped at each breakpoint.
- Calendar, error and success states are reviewed in both dark-image and light-surface contexts.

## Rollout

The redesign should be implemented in slices that keep the reservation path usable:

1. Establish design tokens, page grid, header, footer and hero.
2. Rebuild the availability-first reservation interaction.
3. Add content sections and gallery using verified copy and imagery.
4. Complete responsive, accessibility and performance passes.
5. Run PHPUnit, Pint, build and Playwright in desktop and mobile modes.

## Consequences

- The home becomes specific to the complete-house product and its primary audience.
- Availability becomes clearer and personal data is requested later in the flow.
- Better photography becomes a content dependency, especially for pool, garden and shared spaces.
- The existing reservation backend and domain rules do not need replacement.
- The public Blade and CSS structure will change substantially, so browser coverage is required before release.
