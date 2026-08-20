# Goosialize Cookies — Script Blocking

Goosialize Cookies blocks optional JavaScript by keeping consent-dependent
definitions non-executable until the required consent category is granted.

## Supported categories

The blocker supports:

- `preferences`
- `analytics`
- `marketing`

`necessary` is intentionally excluded. Necessary functionality should load
normally and must not depend on optional consent.

## Inline definition

Use a non-executable script definition with:

- `type="text/plain"`
- `data-goosialize-consent`
- optional `data-goosialize-consent-id`

Example structure:

    <script
        type="text/plain"
        data-goosialize-consent="analytics"
        data-goosialize-consent-id="analytics-inline"
    >
        // analytics code
    </script>

## External definition

For an external script, store the source URL in:

    data-goosialize-consent-src

Example structure:

    <script
        type="text/plain"
        data-goosialize-consent="marketing"
        data-goosialize-consent-id="marketing-provider"
        data-goosialize-consent-src="https://example.com/provider.js"
        async
    ></script>

## Activation contract

A definition executes only when all of the following are true:

1. It uses `type="text/plain"`.
2. It declares a supported optional consent category.
3. The current consent state grants that category.
4. It contains exactly one executable source:
   - inline JavaScript, or
   - `data-goosialize-consent-src`.
5. The definition has not already been activated.

## Fail-closed behavior

The blocker does not execute definitions when:

- consent is unknown;
- consent for the category is denied;
- the category is unknown;
- the definition is empty;
- both inline code and an external source are present.

## Activation identity

`data-goosialize-consent-id` is optional but recommended.

When present, it identifies the logical script definition and prevents
duplicate activation during the current page lifecycle.

## Dynamic definitions

A MutationObserver scans newly inserted DOM content.

New consent-dependent script definitions therefore follow the same
authorization rules as scripts present at initial page load.

## Revocation

Generic JavaScript execution cannot safely be reversed after code has run.

When consent is revoked:

- unexecuted definitions remain blocked;
- newly added matching definitions remain blocked;
- already executed scripts are not re-executed automatically;
- provider-specific teardown and cookie cleanup belong to the Service
  Registry and integration layer.

## Provider neutrality

The blocker contains no Google, Meta, Microsoft, TikTok or other provider
specific behavior.

Provider integrations consume the consent and service contracts separately.
