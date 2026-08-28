# Goosialize Cookies — Troubleshooting

## Consent banner does not appear

First determine whether the browser already has a valid consent decision.

The banner is not expected to behave as an unknown-consent banner after a
valid decision has already been stored.

For testing, use a fresh browser profile/session or remove the applicable
site consent cookie through normal browser developer tools.

Do not change production consent configuration merely to force a test banner.

## Appearance is installed but frontend styling does not change

For Goosialize Cookies Appearance 1.0.0, verify that the FREE Core is:

```text
Goosialize Cookies >= 1.1.1
```

Then:

1. confirm both plugins are enabled;
2. clear Grav cache;
3. reload with a fresh request;
4. verify the Core consent interface still works without Appearance;
5. verify that Appearance configuration contains valid presentation values.

The Core intentionally ignores unsupported or unsafe presentation data.

## Appearance works in Admin2 preview but not on the site

Confirm the installed Core version first.

Core 1.1.1 provides the frontend presentation renderer required by Appearance
1.0.0.

After updating:

```bash
php bin/grav clearcache
```

Then verify the actual site frontend rather than only the Admin2 preview.

## Presentation changes appear stale

Clear Grav cache:

```bash
php bin/grav clearcache
```

Then reload the frontend.

Browser caching may also affect static CSS or JavaScript during development.

## Optional script does not execute

Verify:

1. the script is declared using the supported Goosialize consent attributes;
2. the declared consent category is supported;
3. the visitor has granted that category;
4. the script definition is valid.

Optional scripts fail closed while consent is unknown or denied.

See `SCRIPT-BLOCKING.md`.

## Necessary appears enabled

This is expected.

Necessary is always active and cannot be disabled through granular
preferences.

A presentation addon cannot change this rule.

## Reject All still leaves Necessary enabled

This is expected.

Reject All denies optional categories while Necessary remains active.

## Consent changes after scrolling or navigation

That is not expected Core behaviour.

Goosialize Cookies does not grant consent because of:

- scrolling;
- navigation;
- elapsed time.

Investigate other site JavaScript or custom integrations if consent state
appears to change without an explicit visitor decision.

## Cookie settings remains visible after a decision

This is expected.

The settings control is the Core-owned withdrawal/preferences-access path.

A presentation addon must not remove that capability.

## Disabling Appearance removes consent functionality

That is not expected.

The FREE Core must remain functional without Appearance.

After disabling Appearance:

```bash
php bin/grav clearcache
```

If Accept, Reject, Customize or withdrawal are unavailable after that, treat
the installation as broken rather than as normal addon behaviour.

## Admin2 interface is unavailable

Verify:

- Admin2 is installed where administration is required;
- the current user has the required permissions;
- the plugin is enabled;
- Grav cache has been cleared after an update.

## PHP or JavaScript errors after an update

Confirm that the deployed plugin files all come from the same release.

Do not mix files from different Core versions.

Restore the previous known-good plugin directory if necessary and investigate
the deployment before retrying the update.

## Privacy ownership questions

Goosialize Cookies owns global browser cookie/tracking consent.

Purpose-specific form, newsletter or other personal-data consent remains with
the plugin collecting that data.

See `ECOSYSTEM-PRIVACY-CONTRACT.md`.
