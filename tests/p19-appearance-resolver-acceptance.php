<?php

declare(strict_types=1);

use Goosialize\Cookies\Appearance\AppearanceDefaults;
use Goosialize\Cookies\Appearance\AppearancePreset;
use Goosialize\Cookies\Appearance\AppearancePresetValues;
use Goosialize\Cookies\Appearance\AppearanceResolver;

require_once __DIR__ . "/../classes/Appearance/AppearancePreset.php";
require_once __DIR__ . "/../classes/Appearance/AppearanceDefaults.php";
require_once __DIR__ . "/../classes/Appearance/AppearancePresetValues.php";
require_once __DIR__ . "/../classes/Appearance/AppearanceResolver.php";

$resolver = new AppearanceResolver();

$defaults = $resolver->resolve();

assert($defaults["preset"] === "goosialize");
assert($defaults["banner"]["mode"] === "corner-banner");
assert($defaults["banner"]["surface"]["accent_color"] === "#ffd000");
assert($defaults["mobile"]["breakpoint"] === 760);

$invalidPreset = $resolver->resolve([
    "preset" => "not-real",
]);

assert($invalidPreset["preset"] === "goosialize");

$unknown = $resolver->resolve([
    "evil" => "drop-me",
    "banner" => [
        "unknown_key" => "drop-me-too",
    ],
]);

assert(!array_key_exists("evil", $unknown));
assert(!array_key_exists("unknown_key", $unknown["banner"]));

$partial = $resolver->resolve([
    "banner" => [
        "edge_spacing" => 40,
    ],
]);

assert($partial["banner"]["edge_spacing"] === 40);
assert($partial["banner"]["mode"] === "corner-banner");
assert($partial["preferences"]["max_width"] === 860);

$presetOverride = $resolver->resolve([
    "preset" => "soft",
    "banner" => [
        "surface" => [
            "border_radius" => 30,
        ],
    ],
]);

assert($presetOverride["preset"] === "soft");
assert($presetOverride["banner"]["surface"]["background"] === "#f8f8f8");
assert($presetOverride["banner"]["surface"]["border_radius"] === 30);

$bounded = $resolver->resolve([
    "banner" => [
        "max_width" => 99999,
        "edge_spacing" => -100,
        "surface" => [
            "border_width" => 99,
            "border_radius" => 999,
            "padding" => 1,
        ],
        "animation" => [
            "duration_ms" => 9999,
        ],
    ],
    "preferences" => [
        "animation_duration_ms" => -20,
    ],
    "mobile" => [
        "breakpoint" => 9999,
    ],
]);

assert($bounded["banner"]["max_width"] === 1600);
assert($bounded["banner"]["edge_spacing"] === 0);
assert($bounded["banner"]["surface"]["border_width"] === 8);
assert($bounded["banner"]["surface"]["border_radius"] === 64);
assert($bounded["banner"]["surface"]["padding"] === 8);
assert($bounded["banner"]["animation"]["duration_ms"] === 800);
assert($bounded["preferences"]["animation_duration_ms"] === 0);
assert($bounded["mobile"]["breakpoint"] === 1280);

$wrongTypes = $resolver->resolve([
    "banner" => [
        "max_width" => "999",
    ],
]);

assert($wrongTypes["banner"]["max_width"] === 860);

$custom = $resolver->resolve([
    "preset" => "custom",
]);

assert($custom["preset"] === "custom");
assert($custom["banner"]["surface"]["background"] === "#ffffff");
assert($custom["banner"]["surface"]["border_radius"] === 14);

echo "P19_DEFAULT_RESOLUTION=PASS\n";
echo "P19_INVALID_PRESET_FALLBACK=PASS\n";
echo "P19_UNKNOWN_KEYS_DROPPED=PASS\n";
echo "P19_PARTIAL_CONFIG_INHERITANCE=PASS\n";
echo "P19_PRESET_BEFORE_OVERRIDE=PASS\n";
echo "P19_NUMERIC_CLAMPING=PASS\n";
echo "P19_INVALID_NUMERIC_TYPE_FALLBACK=PASS\n";
echo "P19_CUSTOM_PRESET_NO_OVERRIDES=PASS\n";
echo "P19_D=PASS\n";
