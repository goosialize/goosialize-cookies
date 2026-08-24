# 1.1.0
## 08/24/2026

1. [](#new)
   * Add the presentation-provider runtime bridge for compatible Goosialize Cookies presentation addons.
   * Add a native Admin2 Available Add-ons section with local installed-state discovery.

2. [](#improved)
   * Keep premium presentation implementation outside the FREE consent authority while exposing the shared presentation integration contract.
   * Use the supported API response helper for the Admin2 service registry.

# 1.0.1
## 08/20/2026

1. [](#improved)
   * Declare explicit Grav 2.0 compatibility for GPM.
   * Align release metadata with Grav repository requirements.
   * Add the public GitHub issues URL to plugin metadata.

# 1.0.0
## 08/20/2026

1. [](#new)
   * Initial public release.
   * Add privacy-first cookie consent management with necessary, preferences, analytics and marketing categories.
   * Add equal-access Accept All, Reject All and granular preferences.
   * Add versioned first-party consent persistence, withdrawal and re-consent lifecycle.
   * Add declarative consent-aware script blocking with fail-closed defaults.
   * Add validated service and cookie disclosure with Admin2 service management.
   * Add Google Consent Mode v2 Basic integration.
   * Add a read-only consent consumer contract for Goosialize integrations.
   * Add English and Greek frontend interfaces.
   * Add keyboard, focus, reduced-motion and responsive accessibility hardening.

2. [](#improved)
   * Polish the consent banner and preferences modal for the Goosialize visual system.
   * Keep Accept All and Reject All at equal visual prominence.

3. [](#security)
   * Do not use fingerprinting, cross-site visitor identifiers or hidden telemetry.
   * Do not persist consent in localStorage or similar fallback storage.
