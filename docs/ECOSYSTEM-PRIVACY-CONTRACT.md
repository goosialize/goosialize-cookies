# Goosialize Ecosystem Privacy Contract

## Ownership

### Goosialize Cookies

Goosialize Cookies is the single global authority for:

- cookie consent
- browser storage consent
- analytics consent
- advertising/tracking consent
- consent-aware script activation
- Google Consent Mode state

### Data-collecting plugins

Each Goosialize plugin that collects personal data owns its own
purpose-specific privacy processing and, where applicable, consent.

Examples:

- `goosialize.newsletter.subscription`
- `goosialize.leads.contact_request`
- `goosialize.leads.marketing_email`
- `goosialize.links.contact_request`

Cookie consent MUST NOT be used as newsletter or form consent.

Form consent MUST NOT implicitly grant cookie or tracking consent.

## Ecosystem principles

1. One Goosialize visual language.
2. One privacy vocabulary.
3. One global cookie-consent authority.
4. Purpose-specific form processing.
5. No generic "I agree to GDPR" abstraction.
6. Separate purposes and records.
7. Versioned notices and consents.
8. Withdrawal where consent applies.
9. Common developer-facing contracts.
10. Common Admin2 UX patterns.
11. No duplicate Google consent UI.
12. Privacy by design.
13. No silent Goosialize telemetry.
14. Future Goosialize Hub compatibility.

## Visitor tracking prohibition

The Goosialize ecosystem MUST NOT introduce:

- browser fingerprinting
- cross-site visitor IDs
- hidden consent telemetry
- visitor profiles shared between installations
- remote visitor tracking by Goosialize

unless a future explicitly documented feature has a valid,
transparent and independently reviewed privacy model.
