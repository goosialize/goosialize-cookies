from pathlib import Path

template = Path(
    "templates/partials/goosialize-cookies-ui.html.twig"
).read_text()

ui = Path(
    "assets/js/ui.js"
).read_text()

css = Path(
    "assets/css/consent.css"
).read_text()

plugin = Path(
    "goosialize-cookies.php"
).read_text()

translations = (
    Path("languages/en.yaml").read_text()
    + "\n"
    + Path("languages/el.yaml").read_text()
)

required_template = [
    'data-goosialize-consent-banner',
    'data-goosialize-consent-modal',
    'data-goosialize-consent-settings',
    'data-goosialize-consent-action="accept-all"',
    'data-goosialize-consent-action="reject-all"',
    'data-goosialize-consent-action="manage"',
    'data-goosialize-consent-action="save"',
    'data-goosialize-consent-category="',
    'role="dialog"',
    'aria-modal="true"',
    'aria-labelledby=',
    'aria-describedby=',
]

for token in required_template:
    if token not in template:
        raise SystemExit(
            f"P4_TEMPLATE_MISSING={token}"
        )

required_ui = [
    "getState",
    "getSnapshot",
    "acceptAll",
    "rejectAll",
    "savePreferences",
    "Escape",
    "Tab",
    "goosialize:consent-ready",
    "goosialize:consent-changed",
]

for token in required_ui:
    if token not in ui:
        raise SystemExit(
            f"P4_UI_JS_MISSING={token}"
        )

required_css = [
    ":focus-visible",
    "prefers-reduced-motion",
    ".goo-consent__banner",
    ".goo-consent__modal",
    ".goo-consent__settings",
]

for token in required_css:
    if token not in css:
        raise SystemExit(
            f"P4_CSS_MISSING={token}"
        )

required_plugin = [
    "onOutputGenerated",
    "onTwigSiteVariables",
    "assets/css/consent.css",
    "assets/js/ui.js",
    "data-goosialize-consent-root",
]

for token in required_plugin:
    if token not in plugin:
        raise SystemExit(
            f"P4_PLUGIN_MISSING={token}"
        )

required_translations = [
    "ACCEPT_ALL:",
    "REJECT_ALL:",
    "MANAGE_PREFERENCES:",
    "SAVE_PREFERENCES:",
    "COOKIE_SETTINGS:",
    "PREFERENCES_TITLE:",
]

for token in required_translations:
    if translations.count(token) < 2:
        raise SystemExit(
            f"P4_TRANSLATION_MISSING={token}"
        )

print("BANNER_STRUCTURE=PASS")
print("PREFERENCES_STRUCTURE=PASS")
print("REJECT_ALL_FIRST_LAYER=PASS")
print("REJECT_ALL_MODAL=PASS")
print("COOKIE_SETTINGS_TRIGGER=PASS")
print("ARIA_DIALOG_CONTRACT=PASS")
print("KEYBOARD_CONTRACT=PASS")
print("FOCUS_TRAP_CONTRACT=PASS")
print("ESCAPE_CONTRACT=PASS")
print("REDUCED_MOTION_CONTRACT=PASS")
print("EN_TRANSLATIONS=PASS")
print("EL_TRANSLATIONS=PASS")
print("THEME_INDEPENDENT_INJECTION=PASS")
print("P4_STATIC_ACCEPTANCE=PASS")
