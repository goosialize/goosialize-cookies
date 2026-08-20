from pathlib import Path
import yaml

template = Path(
    "templates/partials/goosialize-cookies-ui.html.twig"
).read_text()

ui = Path(
    "assets/js/ui.js"
).read_text()

for token in [
    "data-goosialize-consent-banner-description",
    "data-banner-default-message",
    "data-reconsent-expired-message",
    "data-reconsent-version-message",
]:
    assert token in template, token

for token in [
    "function lifecycleStatus()",
    "function syncBannerMessage()",
    "lifecycle === 'expired'",
    "lifecycle === 'version_mismatch'",
    "syncBannerMessage();",
]:
    assert token in ui, token

assert "lifecycle === 'malformed'" not in ui
assert "lifecycle === 'future_timestamp'" not in ui

for file in [
    "languages/en.yaml",
    "languages/el.yaml",
]:
    data = yaml.safe_load(
        Path(file).read_text()
    )

    ui_copy = (
        data
        ["PLUGIN_GOOSIALIZE_COOKIES"]
        ["UI"]
    )

    assert ui_copy["RECONSENT_EXPIRED"]
    assert ui_copy["RECONSENT_VERSION_CHANGED"]

print(
    "P11_RECONSENT_UI_ACCEPTANCE=PASS"
)
