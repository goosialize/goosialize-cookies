# Goosialize Cookies — Consent & GDPR

Privacy-first cookie consent and browser-tracking consent infrastructure for
Grav 2 and Admin2.

Goosialize Cookies is the **FREE global cookie and tracking-consent authority**
for the Goosialize ecosystem.

## Features

- Accept All, Reject All and granular preferences.
- Necessary, Preferences, Analytics and Marketing categories.
- Versioned first-party consent cookie.
- Consent expiry and re-consent handling.
- Withdrawal without page reload.
- Consent-aware script blocking.
- Service, cookie and browser-storage registry.
- Visitor-facing service disclosure.
- Admin2 service management.
- Google Consent Mode v2.
- Read-only ecosystem consent consumer API.
- English and Greek.
- Keyboard and screen-reader accessibility.
- Mobile viewport and overflow hardening.
- Presentation-provider integration for compatible presentation addons.

## FREE Core and PAID addons

**Goosialize Cookies** is the FREE Core product.

The Core always owns:

- consent categories and decisions;
- Accept All, Reject All and granular preferences;
- consent persistence and lifecycle;
- withdrawal and re-consent;
- consent-aware script blocking;
- service disclosure;
- Google Consent Mode state;
- the canonical accessible consent interface.

Privacy-essential consent functionality is not gated behind a paid addon.

### Goosialize Cookies Appearance

**Goosialize Cookies Appearance** is a separately distributed **PAID
presentation addon**.

Appearance may enhance:

- banner layout;
- positioning and spacing;
- surface styling;
- button presentation;
- preferences-dialog presentation;
- settings-button presentation;
- responsive presentation behavior.

Appearance **does not** own or modify consent semantics, consent persistence,
category defaults, script-blocking decisions or withdrawal capability.

Goosialize Cookies Appearance 1.0.0 requires **Goosialize Cookies 1.1.1 or later**
for complete frontend presentation rendering.

See:

- `docs/APPEARANCE-ADDON.md`
- `docs/addon-ecosystem-contract.md`

## Privacy model

Goosialize Cookies is the global browser cookie and tracking-consent authority
for the Goosialize ecosystem.

It does not replace purpose-specific form, newsletter or marketing consent.
Those remain owned by the plugin collecting the relevant personal data.

The plugin does not use:

- fingerprinting;
- cross-site visitor identifiers;
- hidden consent telemetry;
- server-side visitor tracking;
- browser-storage fallback for consent persistence.

See `docs/ECOSYSTEM-PRIVACY-CONTRACT.md`.

## Consent categories

- **Necessary** — always active and required for website operation.
- **Preferences** — optional visitor preference technologies.
- **Analytics** — optional website measurement technologies.
- **Marketing** — optional advertising and marketing measurement technologies.

Necessary consent cannot be disabled.

Optional categories remain denied until the visitor grants them.

## Script blocking

Optional scripts can be declared in a non-executable form and activated only
after the corresponding category has been granted.

Unknown, invalid or non-granted optional categories fail closed.

See `docs/SCRIPT-BLOCKING.md`.

## Service registry

Services may declare:

- provider;
- consent category;
- purpose;
- privacy URL;
- expected cookies and durations;
- browser-storage keys.

The registry describes services. It does not grant consent or activate
providers.

See `docs/SERVICE-REGISTRY.md`.

## Google Consent Mode v2

The optional Google integration maps Goosialize consent to:

- `analytics_storage`;
- `ad_storage`;
- `ad_user_data`;
- `ad_personalization`.

Signals default to denied.

Goosialize Cookies itself does not load Google Analytics, Google Ads or Google
Tag Manager scripts.

## Administration

The plugin integrates with Grav 2 Admin2 and provides validated service
registry management with separate read and service-management permissions.

Compatible Goosialize addons may also be listed in the native Admin2 plugin
interface without transferring consent authority to those addons.

## Requirements

- Grav 2
- PHP 8.3 or later
- Admin2 for the administration interface

## Installation

Install the plugin directory as:

```text
user/plugins/goosialize-cookies
```

Enable the plugin in Grav configuration.

Configure the privacy-policy URL, consent lifetime and optional service
declarations as required by the site.

For installation and upgrade guidance, see:

`docs/INSTALLATION.md`

## Upgrade to 1.1.1

Version 1.1.1 adds the Core-owned frontend presentation renderer used by
compatible presentation addons.

Existing valid consent state does not need to be replaced solely because the
presentation layer changes.

If Goosialize Cookies Appearance is installed, update the Core to **1.1.1 or later**
before relying on Appearance frontend presentation.

## Configuration

The Core configuration remains the authority for privacy and consent
behaviour.

Presentation addons must not overwrite consent configuration.

Technical configuration references:

- Service registry: `docs/SERVICE-REGISTRY.md`
- Script blocking: `docs/SCRIPT-BLOCKING.md`
- Ecosystem privacy ownership: `docs/ECOSYSTEM-PRIVACY-CONTRACT.md`
- Addon ownership: `docs/addon-ecosystem-contract.md`

## Withdrawal

Visitors who have already made a decision retain access to the Core-owned
cookie-settings control.

A presentation addon must not remove or disable this withdrawal path.

## Troubleshooting

See `docs/TROUBLESHOOTING.md`.

The troubleshooting guide covers:

- the banner not appearing;
- the PAID Appearance addon not affecting the frontend;
- blocked optional scripts;
- consent reset/re-consent expectations;
- Admin2 availability;
- cache-related presentation changes.

## Languages

- English
- Greek

## License

Goosialize Cookies Core is licensed under the MIT License.

See `LICENSE`.

Commercial Goosialize addons are distributed and licensed separately.
