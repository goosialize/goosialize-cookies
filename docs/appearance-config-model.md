# Goosialize Cookies — Appearance Configuration Model

Target release: **1.1.0**

## 1. Namespace

All presentation configuration lives below `appearance`.

## 2. Top-level structure

- preset
- banner
- preferences
- settings_button
- mobile

## 3. Preset

Allowed values:

- goosialize
- minimal
- classic
- soft
- high-contrast
- custom

Default: `goosialize`

## 4. Banner

Namespace: `appearance.banner`

### 4.1 Display

Fields:

- mode
- position
- width
- max_width
- min_height
- edge_spacing
- content_alignment

Allowed `mode` values:

- popup
- full-width-bottom
- corner-banner
- full-width-top
- compact-floating

Default mode: `corner-banner`

Allowed `position` values:

- bottom-left
- bottom-right
- center
- top
- bottom

Default position: `bottom-left`

Numeric layout values MUST be bounded and normalized.

### 4.2 Content

Namespace: `appearance.banner.content`

Fields:

- title
- message
- privacy_policy_label
- accept_all_label
- reject_all_label
- manage_preferences_label

Content fields MAY be localized.

Empty content fields MUST resolve to plugin translation defaults.

### 4.3 Surface

Namespace: `appearance.banner.surface`

Fields:

- background
- text_color
- muted_text_color
- accent_color
- border_color
- border_width
- border_radius
- shadow
- padding
- backdrop_enabled
- backdrop_color
- backdrop_opacity

Color values MUST use validated structured color values.

Raw CSS MUST NOT be accepted.

Allowed shadow values:

- none
- small
- medium
- large

### 4.4 Typography

Namespace: `appearance.banner.typography`

Fields:

- title_size
- title_weight
- message_size
- message_weight
- button_size
- line_height
- text_alignment

Allowed text alignment values:

- left
- center
- right

Typography values MUST be selected from bounded design tokens.

Arbitrary font-family injection MUST NOT be supported.

### 4.5 Buttons

Namespace: `appearance.banner.buttons`

Global fields:

- layout
- alignment
- gap
- height
- border_radius
- font_size
- font_weight

Allowed button layout values:

- row
- column
- wrap

Per-action namespaces:

- accept_all
- reject_all
- manage_preferences

Per-action fields:

- background
- text_color
- border_color
- hover_background
- hover_text_color
- icon_enabled
- icon_name
- icon_position

Accept All and Reject All MUST remain comparably prominent.

The configuration resolver MAY normalize unsafe action styling.

### 4.6 Icon

Namespace: `appearance.banner.icon`

Fields:

- enabled
- name
- position
- size
- color
- local_media

Allowed built-in icon values:

- none
- shield
- lock
- cookie
- privacy
- settings
- check

Custom icons MUST use validated local media references.

Raw SVG or executable markup MUST NOT be accepted.

### 4.7 Animation

Namespace: `appearance.banner.animation`

Fields:

- entry
- exit
- duration_ms

Allowed animation values:

- none
- fade
- slide-up
- slide-down
- slide-left
- slide-right
- scale-fade

Animation duration range: 0 to 800 milliseconds.

`prefers-reduced-motion` always overrides decorative animation.

## 5. Preferences

Namespace: `appearance.preferences`

Fields:

- width
- max_width
- max_height
- background
- text_color
- muted_text_color
- accent_color
- border_color
- border_width
- border_radius
- shadow
- backdrop_enabled
- backdrop_color
- backdrop_opacity
- header_spacing
- category_spacing
- service_card_style
- toggle_style
- sticky_footer
- entry_animation
- exit_animation
- animation_duration_ms

Width and height values MUST be bounded.

The preferences interface MUST remain keyboard accessible and scrollable.

Sticky actions MUST remain reachable on supported viewport sizes.

### 5.1 Preferences buttons

Namespace: `appearance.preferences.buttons`

Actions:

- save_preferences
- reject_all
- accept_all

Per-action fields:

- background
- text_color
- border_color
- hover_background
- hover_text_color
- icon_enabled
- icon_name
- icon_position

Accept All and Reject All MUST remain comparably prominent.

Save Preferences MAY use secondary visual prominence.

## 6. Settings button

Namespace: `appearance.settings_button`

Fields:

- enabled
- form
- position
- label
- icon_name
- background
- text_color
- border_color
- border_width
- border_radius
- size
- edge_spacing
- shadow

Allowed `form` values:

- text
- icon
- icon-text

Allowed `position` values:

- bottom-left
- bottom-right

If this control is disabled, another valid withdrawal path MUST remain available.

## 7. Mobile

Namespace: `appearance.mobile`

Fields:

- breakpoint
- banner_mode
- full_width
- edge_spacing
- padding
- stack_buttons
- content_alignment
- settings_button_position

Allowed `banner_mode` values:

- inherit
- bottom-card
- bottom-sheet
- full-width-bottom
- popup

Default mobile mode: `bottom-card`

Mobile configuration MUST prevent horizontal overflow, clipped actions,
unreachable controls and inaccessible modal content.

## 8. Shared validation rules

All numeric values MUST be bounded.

All enum values MUST be validated against explicit allowlists.

All colors MUST use validated structured color values.

All local media references MUST resolve to allowed local media.

Raw HTML, CSS, JavaScript, Twig and executable SVG are forbidden.

Invalid appearance configuration MUST fail safely to normalized defaults.

Appearance configuration MUST never modify consent state or category defaults.

## 9. Default appearance values

The default appearance MUST preserve the latest 1.0.x visual behavior
as closely as practical.

Default preset: `goosialize`

### 9.1 Banner defaults

- mode: corner-banner
- position: bottom-left
- max_width: 860
- edge_spacing: 24
- content_alignment: left
- background: #ffffff
- text_color: #171717
- muted_text_color: #666666
- accent_color: #ffd000
- border_color: rgba-safe-neutral
- border_width: 1
- border_radius: 14
- shadow: medium
- padding: 24
- backdrop_enabled: false
- title_size: medium
- message_size: small
- button layout: row
- button height: 46
- Accept All prominence: primary-neutral
- Reject All prominence: primary-neutral
- Manage Preferences prominence: secondary-outline
- icon enabled: false
- animation entry: fade
- animation exit: fade
- animation duration_ms: 220

### 9.2 Preferences defaults

- max_width: 860
- max_height: 86dvh
- background: #ffffff
- text_color: #171717
- accent_color: #ffd000
- border_radius: 14
- shadow: medium
- backdrop_enabled: true
- sticky_footer: true
- Save Preferences prominence: secondary-outline
- Reject All prominence: primary-neutral
- Accept All prominence: primary-neutral
- animation duration_ms: 220

### 9.3 Settings button defaults

- enabled: true
- form: icon-text
- position: bottom-left
- icon_name: settings
- edge_spacing: 20

### 9.4 Mobile defaults

- breakpoint: 760
- banner_mode: bottom-card
- full_width: true
- edge_spacing: 12
- stack_buttons: true
- content_alignment: left

## 10. Upgrade and migration behavior

Existing 1.0.x installations do not contain an `appearance` namespace.

When `appearance` is absent, the 1.1.0 resolver MUST construct normalized
default appearance values without writing configuration automatically.

A normal frontend request MUST NOT mutate plugin configuration.

Saving Appearance settings through Admin2 MAY persist the resolved values.

The migration layer MUST NOT modify:

- consent.version
- consent.lifetime_days
- consent.cookie_name
- category enabled state
- category required state
- category default state
- policy_url
- service registry definitions
- Google Consent Mode configuration
- consent cookie values

No existing valid 1.0.x consent cookie requires replacement solely because
of the introduction of the Presentation System.

## 11. Partial configuration resolution

Appearance configuration MAY be partial.

Missing values MUST inherit from normalized defaults.

Unknown values MUST NOT pass directly to rendering.

Invalid enum, numeric, color, media or animation values MUST resolve to
safe defaults or normalized bounded values.

User configuration MUST NOT be merged in a way that permits arbitrary
keys to become CSS properties, HTML attributes, JavaScript or templates.

## 12. Preset resolution

A preset supplies presentation values only.

Preset resolution order:

1. system defaults
2. selected preset
3. validated administrator overrides
4. safety normalization

The final renderer consumes only the resolved normalized configuration.

Changing a preset MUST NOT change consent state, consent persistence,
service definitions, script blocking or integrations.

## 13. Resolver contract

The Appearance resolver MUST return a complete normalized structure.

The renderer MUST NOT read arbitrary raw configuration directly.

The resolver is responsible for:

- defaults
- preset application
- partial configuration merging
- enum validation
- numeric bounds
- color validation
- media validation
- animation bounds
- privacy guardrail normalization
- accessibility guardrail normalization
- responsive normalization

Consent Engine configuration MUST remain outside the resolver scope.
