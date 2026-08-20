# Goosialize Cookies — Service & Cookie Registry

The Service Registry is the canonical description layer for optional
services used by Goosialize Cookies.

It does not grant consent and it does not activate providers by itself.

## Ownership

Goosialize Cookies owns browser cookie and tracking consent.

The registry describes which optional services depend on that consent.

Form-specific privacy purposes remain owned by the plugin collecting
the relevant personal data.

## Supported consent categories

Consent-managed services may use:

- `preferences`
- `analytics`
- `marketing`

`necessary` is intentionally excluded from the consent-managed service
registry.

Necessary website functionality should load normally and must not be
misrepresented as optional consent.

## Service definition

A service has:

- stable ID
- display name
- provider
- consent category
- purpose
- enabled state
- optional privacy-policy URL
- zero or more cookie declarations
- zero or more browser-storage declarations

## Service IDs

IDs are stable machine identifiers.

Examples:

    google.analytics
    meta.pixel
    example.preferences

IDs accept lowercase letters, digits, dots, underscores and hyphens.

## Cookie declarations

A cookie declaration contains:

- name
- purpose
- optional human-readable duration

Examples:

    _ga
    _ga_*
    _fbp

Cookie declarations describe expected behavior. They are not a scanner
result and do not claim that a cookie is always present.

## Browser storage declarations

Supported storage types are:

- `cookie`
- `local_storage`
- `session_storage`
- `indexed_db`

A storage declaration contains:

- storage type
- key
- purpose

## Config loading

Service configuration is converted through `ServiceConfigLoader`.

Configuration never bypasses the domain objects.

The loader validates:

- service category
- required strings
- enabled boolean
- cookie declarations
- storage declarations
- storage type
- privacy URL
- service ID

## Fail-closed behavior

Invalid service definitions are rejected.

Examples include:

- unknown consent category
- use of the `necessary` category
- invalid service ID
- invalid privacy URL
- unknown storage type
- non-boolean enabled value
- malformed cookie or storage definitions

## Provider neutrality

The registry may contain provider metadata such as Google or Meta, but
the registry itself contains no provider activation logic.

Provider-specific execution belongs to dedicated integration layers.

## Future cleanup support

The registry is the foundation for future provider-specific cleanup.

Revocation may later use declared cookie/storage metadata to remove
eligible first-party state where technically safe.

The registry must not claim that already transmitted third-party data
can be remotely erased merely by deleting browser state.
