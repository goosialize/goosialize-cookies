# Goosialize Cookies Appearance — PAID Addon

## Product boundary

Goosialize Cookies Appearance is a separately distributed **PAID presentation
addon** for Goosialize Cookies.

The FREE Core remains the global browser cookie and tracking-consent authority.

Appearance is not a second consent engine.

## Compatibility

For Goosialize Cookies Appearance 1.0.0 frontend presentation, use:

```text
Goosialize Cookies Core >= 1.1.1
```

Core 1.1.1 provides the Core-owned whitelist renderer that safely consumes
normalized presentation-provider values on the real frontend consent
interface.

## What Appearance may control

Appearance may provide normalized presentation values for:

- banner mode;
- banner position;
- spacing;
- surfaces;
- borders and radius;
- typography presentation;
- button presentation;
- preferences-dialog presentation;
- cookie-settings button presentation;
- responsive desktop/tablet/mobile presentation.

## What Appearance must never control

Appearance must not own or change:

- consent categories;
- Necessary-category semantics;
- Accept All semantics;
- Reject All semantics;
- granular consent decisions;
- consent persistence;
- consent expiry;
- re-consent lifecycle;
- withdrawal capability;
- script-blocking decisions;
- Google Consent Mode consent state;
- service consent authority.

These remain Core-owned.

## Security boundary

The Core renderer consumes only known, bounded presentation values.

A presentation provider cannot pass arbitrary:

- HTML;
- CSS;
- JavaScript;
- Twig;
- URL-based CSS execution.

Invalid or unsupported presentation values are rejected or normalized by the
Core presentation boundary.

## Core-only behaviour

If Appearance is absent, disabled or unavailable, the FREE Core continues to
render its canonical consent interface.

The visitor must retain:

- Accept All;
- Reject All;
- Customize;
- granular preferences;
- withdrawal/settings access.

## Responsive behaviour

Appearance may supply responsive presentation metadata.

The Core applies those values through its bounded presentation layer.

Responsive styling does not change the visitor's consent state.

## Installation order

Recommended order:

1. install/update Goosialize Cookies Core to 1.1.1 or later;
2. confirm normal Core consent operation;
3. install Goosialize Cookies Appearance;
4. enable the addon;
5. configure presentation;
6. clear Grav cache;
7. verify frontend presentation;
8. re-check Accept, Reject, Customize and withdrawal.

## Commercial boundary

The FREE Core is MIT-licensed.

Goosialize Cookies Appearance is commercially distributed and licensed
separately.

Commercial presentation features must never become a prerequisite for basic
privacy-essential consent functionality.

See also `addon-ecosystem-contract.md`.
