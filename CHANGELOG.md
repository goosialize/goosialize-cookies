# Changelog

All notable changes to Goosialize Cookies are documented here.

## 1.0.0

Initial public release.

### Consent

- Four consent categories: necessary, preferences, analytics and marketing.
- Equal-access Accept All, Reject All and Manage Preferences actions.
- Versioned first-party consent cookie.
- Configurable consent lifetime.
- Consent withdrawal without page reload.
- Fail-closed handling of missing, malformed, expired, future-dated and
  outdated-version consent.
- Re-consent messaging for expired consent and consent-version changes.

### Privacy

- Goosialize Cookies is the browser cookie and tracking consent authority for
  the Goosialize ecosystem.
- No fingerprinting.
- No cross-site visitor identifier.
- No server-side visitor tracking.
- No localStorage or similar fallback for consent persistence.
- Purpose-specific form and marketing consent remain owned by the plugin that
  collects the relevant personal data.

### Script blocking

- Consent-aware declarative blocking for preferences, analytics and marketing.
- Dynamic script-definition support.
- Fail-closed behavior before consent.
- No provider-specific tracking is bundled into the blocker.

### Service and cookie registry

- Validated Service Registry.
- Cookie and browser-storage declarations.
- Admin2 service management.
- Visitor-facing disclosure grouped by consent category.
- Provider, purpose, privacy URL, cookie duration and browser-storage details.

### Google integration

- Google Consent Mode v2 bridge.
- Default-denied consent signals.
- Live consent updates and withdrawal handling.
- Read-only Goosialize consent consumer contract.
- Goosialize Google integration without a second consent state or banner.
- No Google tracking tag loader included in this release.

### Accessibility

- Keyboard-accessible first layer and preferences interface.
- Modal focus containment and focus return.
- Tab and Shift+Tab wrapping.
- Escape and backdrop close.
- Screen-reader dialog semantics and live status messaging.
- Reduced-motion support.
- Mobile dynamic viewport and overscroll hardening.
- Long-content and disclosure overflow containment.

### Languages

- English.
- Greek.
