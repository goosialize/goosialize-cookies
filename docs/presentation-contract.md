# Goosialize Cookies — Presentation System Contract

Target release: **1.1.0**

## 1. Architectural boundary

Goosialize Cookies separates:

- Consent Engine
- Presentation Engine

The Presentation Engine MUST NOT modify consent semantics, storage, lifecycle,
script blocking, service registry behavior, Google Consent Mode behavior,
consumer API behavior, or privacy rules.

## 2. Supported display modes

Version 1.1.0 supports:

- popup
- full-width-bottom
- corner-banner
- full-width-top
- compact-floating

Desktop modes MAY resolve to a safer mobile presentation where required.

## 3. Banner editor

Configurable domains:

- display
- content
- surface
- typography
- buttons
- icons
- animation
- responsive behavior

## 4. Preferences editor

Configurable domains:

- modal width
- maximum height
- surface
- typography
- service cards
- toggles
- sticky footer
- buttons
- backdrop
- animation

## 5. Persistent settings control

Supported forms:

- text button
- icon button
- icon and text

Supported positions:

- bottom-left
- bottom-right

## 6. Responsive and mobile

The presentation system MUST prevent:

- horizontal overflow
- unreachable actions
- clipped consent choices
- inaccessible modal content
- hidden withdrawal controls

A corner banner MAY become a bottom card or bottom sheet on narrow screens.

## 7. Presets

Initial presets:

- Goosialize
- Minimal
- Classic
- Soft
- High Contrast
- Custom

Presets are configuration templates only.

After applying a preset, exposed presentation properties remain editable.

## 8. Upgrade compatibility

Absence of new appearance configuration MUST resolve to safe defaults.

Upgrade from 1.0.x MUST NOT change:

- consent meaning
- consent cookie format
- consent lifecycle
- category defaults
- script blocking
- Google Consent Mode behavior
- service definitions

## 9. Privacy and dark-pattern guardrails

The Appearance system MUST NOT permit:

- Hide Reject All
- Hide Accept All
- Make optional consent active by default
- Automatically accept consent after a timeout
- Accept consent on scroll
- Accept consent on navigation
- Accept consent through inactivity
- Convert Necessary into optional consent
- Remove consent withdrawal capability
- Add tracking scripts through appearance configuration
- Change consent state through presentation configuration
- Introduce fingerprinting
- Introduce cross-site visitor identifiers
- Introduce hidden telemetry

Accept All and Reject All MUST remain comparably prominent.

Unsafe visual combinations MAY be normalized by the renderer.

## 10. Accessibility guardrails

Appearance customization MUST preserve:

- keyboard operation
- visible focus
- dialog semantics
- focus containment
- focus return
- Escape behavior
- readable contrast
- usable touch targets
- screen-reader labels
- reduced-motion preferences

`prefers-reduced-motion` MUST override decorative animation.

## 11. Security boundary

The standard appearance editor MUST NOT expose:

- arbitrary JavaScript
- arbitrary HTML
- arbitrary Twig
- arbitrary executable SVG
- remote script injection
- arbitrary CSS injection

Appearance configuration is structured data only.

## 12. Admin2 contract

Appearance configuration MUST use native Admin2 controls.

Expected top-level areas:

- General
- Services
- Appearance
- Integrations
- Privacy

Expected Appearance sections:

- Banner
- Preferences
- Settings Button
- Mobile
- Presets

## 13. Configuration namespace

Presentation settings live below:

`appearance`

Proposed high-level structure:

```yaml
appearance:
  preset: goosialize

  banner:
    mode: corner-banner
    position: bottom-left
    content: {}
    surface: {}
    typography: {}
    buttons: {}
    icon: {}
    animation: {}

  preferences: {}
  settings_button: {}
  mobile: {}
```

The exact schema is defined during P18.

## 14. Non-goals for 1.1.0

The Presentation System does not add:

- cookie scanning
- automatic tracker discovery
- arbitrary script editing
- consent analytics
- visitor profiling
- geo-targeting
- IAB TCF
- CMP advertising frameworks

## 15. Definition of success

Goosialize Cookies 1.1.0 succeeds when an administrator can build visually
distinct, responsive, brand-aligned consent experiences without changing or
weakening the Consent Engine.
