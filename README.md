# Goosialize Cookies — Consent & GDPR

Privacy-first cookie consent and browser-tracking consent infrastructure for
Grav 2 and Admin2.

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

## Script blocking

Optional scripts can be declared in a non-executable form and activated only
after the corresponding category has been granted.

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

## Requirements

- Grav 2
- PHP 8.3 or later
- Admin2 for the administration interface

## Installation

Install the plugin as:

`user/plugins/goosialize-cookies`

Enable it in Grav configuration and configure the privacy-policy URL,
consent lifetime and optional service declarations as required.

## Languages

- English
- Greek

## License

MIT. See `LICENSE`.
