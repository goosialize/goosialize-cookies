# Goosialize Cookies — Addon Ecosystem Contract

Target generation: 1.x ecosystem

## 1. Products

### Goosialize Cookies

FREE core and global browser consent authority.

Owns:

- consent categories and decisions
- consent persistence and lifecycle
- withdrawal and re-consent
- consent-aware script blocking
- service and cookie disclosure
- Google Consent Mode consent bridge
- accessible canonical consent UI

### Goosialize Cookies Appearance

PAID presentation addon.

Owns:

- banner display modes
- presentation presets
- advanced colors and surfaces
- typography and button styling
- icons and animations
- preferences presentation
- settings-button presentation
- mobile presentation overrides

It MUST NOT own or persist consent state.

### Goosialize Cookies Analytics

PAID consent analytics addon.

Owns aggregated consent metrics such as:

- Accept All decisions
- Reject All decisions
- Custom decisions
- preferences opens
- withdrawals
- re-consent events
- consent trends
- language breakdowns
- presentation breakdowns when available

It MUST NOT create visitor profiles, fingerprints, cross-site IDs or hidden telemetry.

## 2. Goosialize Analytics integration

Goosialize Analytics MAY consume aggregated consent analytics when
Goosialize Cookies Analytics is installed and enabled.

Goosialize Cookies Analytics remains the source of truth.

Goosialize Analytics MUST NOT maintain a second consent analytics store.

Integration MUST be read-only from the Goosialize Analytics side.

## 3. Appearance dependency boundary

Goosialize Cookies Appearance depends on Goosialize Cookies.

For Goosialize Cookies Appearance 1.0.0 frontend presentation, the
minimum supported FREE Core version is Goosialize Cookies 1.1.1.

The FREE plugin remains fully functional without the Appearance addon.

The Appearance addon consumes a presentation extension contract and
MUST NOT alter consent semantics, consent storage, category defaults,
script-blocking decisions or integration consent state.

## 4. Analytics dependency boundary

Goosialize Cookies Analytics depends on Goosialize Cookies.

Analytics collection MUST be privacy-preserving and aggregated.

No IP address, fingerprint, persistent visitor identifier or cross-site
identifier is required for consent analytics.

The FREE plugin remains fully functional without the Analytics addon.

## 5. Optional cross-addon integration

When both paid addons are active, Cookies Analytics MAY record the
resolved presentation mode or preset as an aggregated reporting dimension.

This MUST NOT create a visitor-level profile.

## 6. Extraction rule

The advanced Presentation System work introduced during P17-P19 will be
extracted into Goosialize Cookies Appearance without rewriting published
or existing Git history.

The FREE repository will retain only the minimum stable extension contract
needed for paid presentation providers to integrate safely.

## 7. Commercial boundary

Privacy-essential consent functionality MUST remain available in FREE.

Paid addons enhance presentation or reporting; they MUST NOT gate
fundamental consent, rejection, preference management or withdrawal.
