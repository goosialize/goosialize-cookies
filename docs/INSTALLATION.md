# Goosialize Cookies — Installation & Upgrade

## Requirements

Goosialize Cookies Core requires:

- Grav 2
- PHP 8.3 or later
- Admin2 when the administration interface is required

## Install the FREE Core

Install the plugin into:

```text
user/plugins/goosialize-cookies
```

The resulting plugin entry point must be available at:

```text
user/plugins/goosialize-cookies/goosialize-cookies.php
```

Enable Goosialize Cookies in Grav configuration.

After installation, configure the site's privacy-policy URL, consent lifetime
and any required service declarations.

## Clear Grav cache

After installing or updating plugin files, clear Grav's cache from the Grav
root:

```bash
php bin/grav clearcache
```

## Verify the frontend

On a visitor session without an existing valid consent decision:

1. the consent interface should appear;
2. Necessary must remain enabled;
3. Preferences, Analytics and Marketing must remain optional;
4. Accept All must grant the optional categories;
5. Reject All must leave optional categories denied;
6. Customize must allow granular selection;
7. a cookie-settings control must remain available after a decision.

No consent should be granted merely because the visitor scrolls, navigates or
waits on the page.

## Upgrade to Core 1.1.1

Goosialize Cookies 1.1.1 adds the Core-owned frontend presentation renderer
for compatible presentation providers.

The update does not transfer consent authority to a presentation addon.

Existing valid consent state does not require replacement solely because the
presentation layer changes.

After upgrading:

```bash
php bin/grav clearcache
```

Then verify the frontend with a fresh visitor session.

## Goosialize Cookies Appearance

Goosialize Cookies Appearance is a separate PAID addon.

For Appearance 1.0.0 frontend presentation, install or update the FREE Core to
**Goosialize Cookies 1.1.1 or later**.

Recommended order:

1. install or update Goosialize Cookies Core;
2. verify Core consent behaviour;
3. install Goosialize Cookies Appearance;
4. enable and configure Appearance;
5. clear Grav cache;
6. verify desktop, tablet and mobile presentation;
7. verify that Accept, Reject, Customize and withdrawal still behave exactly
   as Core-owned consent actions.

See `APPEARANCE-ADDON.md`.

## Core-only fallback

The FREE Core remains functional when Appearance:

- is not installed;
- is disabled;
- cannot provide valid presentation data.

Consent functionality must not depend on the paid presentation addon.

## Updating safely

Before updating a production installation:

1. back up the existing plugin directory;
2. back up site configuration;
3. deploy the exact intended plugin version;
4. clear Grav cache;
5. verify consent behaviour;
6. verify optional script blocking;
7. verify withdrawal;
8. verify the frontend.

Do not replace consent configuration merely to apply presentation changes.

## Uninstalling Appearance

Removing the Appearance addon must not remove or invalidate the FREE Core
consent authority.

After removing or disabling Appearance:

```bash
php bin/grav clearcache
```

The canonical Core consent UI should remain available.

## Uninstalling the Core

Goosialize Cookies is the global browser cookie/tracking-consent authority for
the Goosialize ecosystem.

Do not remove the Core while other site functionality expects its consent
contract.

Review dependent integrations before uninstalling it.
