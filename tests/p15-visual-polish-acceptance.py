from pathlib import Path

template = Path(
    "templates/partials/goosialize-cookies-ui.html.twig"
).read_text()

css = Path(
    "assets/css/consent.css"
).read_text()

optional_toggle = template.index(
    '<label class="goo-consent__toggle">'
)

optional_disclosure = template.index(
    """category: category"""
)

assert optional_toggle < optional_disclosure

necessary_control = template.index(
    '<div class="goo-consent__category-control">'
)

necessary_disclosure = template.index(
    """category: 'necessary'"""
)

assert necessary_control < necessary_disclosure

required_css = [
    "width: min(860px, 100%)",
    "position: sticky",
    "background: var(--goo-consent-accent)",
    ".goo-consent__service[open]",
    ".goo-consent__service-summary::after",
    "safe-area-inset-bottom",
    "94dvh",
]

for token in required_css:
    assert token in css, token

print(
    "CATEGORY_CONTROL_BEFORE_DISCLOSURE=PASS"
)

print(
    "MODAL_WIDE_LAYOUT=PASS"
)

print(
    "STICKY_MODAL_ACTIONS=PASS"
)

print(
    "GOOSIALIZE_YELLOW_ACCENT=PASS"
)

print(
    "SERVICE_CARD_POLISH=PASS"
)

print(
    "MOBILE_SAFE_AREA=PASS"
)

print(
    "P15_VISUAL_POLISH_ACCEPTANCE=PASS"
)
