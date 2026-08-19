<?php

declare(strict_types=1);

spl_autoload_register(
    static function (string $class): void {
        $prefix = 'Goosialize\\Cookies\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));

        $file = dirname(__DIR__) .
            '/classes/' .
            str_replace('\\', '/', $relative) .
            '.php';

        if (is_file($file)) {
            require $file;
        }
    }
);

use Goosialize\Cookies\Consent\ConsentCategory;
use Goosialize\Cookies\Consent\ConsentDecision;
use Goosialize\Cookies\Consent\ConsentEngine;
use Goosialize\Cookies\Consent\ConsentSelection;
use Goosialize\Cookies\Consent\ConsentSerializer;
use Goosialize\Cookies\Consent\ConsentState;
use Goosialize\Cookies\Consent\ConsentVersion;
use Goosialize\Cookies\Integration\Google\ConsentMode;

function assertTrue(bool $value, string $label): void
{
    if (!$value) {
        throw new RuntimeException("FAIL=$label");
    }

    echo "$label=PASS\n";
}

$version = new ConsentVersion(1);
$engine = new ConsentEngine($version);

$accept = $engine->decide(
    ConsentDecision::AcceptAll,
    recordedAt: new DateTimeImmutable(
        '2026-08-19T18:00:00+00:00'
    )
);

assertTrue(
    $accept->state() === ConsentState::AcceptedAll,
    'ACCEPT_ALL_STATE'
);

foreach (ConsentCategory::cases() as $category) {
    assertTrue(
        $accept->granted($category),
        'ACCEPT_ALL_' . strtoupper($category->value)
    );
}

$reject = $engine->decide(
    ConsentDecision::RejectOptional
);

assertTrue(
    $reject->state() === ConsentState::RejectedOptional,
    'REJECT_OPTIONAL_STATE'
);

assertTrue(
    $reject->granted(ConsentCategory::Necessary),
    'NECESSARY_ALWAYS_GRANTED'
);

foreach ([
    ConsentCategory::Preferences,
    ConsentCategory::Analytics,
    ConsentCategory::Marketing,
] as $category) {
    assertTrue(
        !$reject->granted($category),
        'REJECT_' . strtoupper($category->value)
    );
}

$custom = $engine->decide(
    ConsentDecision::Custom,
    [
        'necessary' => false,
        'preferences' => true,
        'analytics' => true,
        'marketing' => false,
    ],
    new DateTimeImmutable(
        '2026-08-19T18:00:00+00:00'
    )
);

assertTrue(
    $custom->state() === ConsentState::Custom,
    'CUSTOM_STATE'
);

assertTrue(
    $custom->granted(ConsentCategory::Necessary),
    'CUSTOM_CANNOT_DISABLE_NECESSARY'
);

$unknownRejected = false;

try {
    ConsentSelection::custom([
        'analytics' => true,
        'unknown' => true,
    ]);
} catch (InvalidArgumentException) {
    $unknownRejected = true;
}

assertTrue(
    $unknownRejected,
    'UNKNOWN_CATEGORY_REJECTED'
);

$nonBooleanRejected = false;

try {
    ConsentSelection::custom([
        'analytics' => 1,
    ]);
} catch (InvalidArgumentException) {
    $nonBooleanRejected = true;
}

assertTrue(
    $nonBooleanRejected,
    'NON_BOOLEAN_REJECTED'
);

$serializer = new ConsentSerializer();
$encoded = $serializer->serialize($custom);
$restored = $serializer->deserialize(
    $encoded,
    $version
);

assertTrue(
    $restored !== null,
    'SERIALIZATION_RESTORE'
);

assertTrue(
    $restored?->selection->toArray() ===
        $custom->selection->toArray(),
    'SERIALIZATION_ROUNDTRIP'
);

assertTrue(
    $serializer->deserialize(
        $encoded,
        new ConsentVersion(2)
    ) === null,
    'VERSION_INVALIDATION'
);

assertTrue(
    $serializer->deserialize(
        '{bad-json',
        new ConsentVersion(1)
    ) === null,
    'MALFORMED_PAYLOAD_REJECTED'
);

$google = new ConsentMode();

$mapping = $google->map(
    $custom->selection->toArray()
);

assertTrue(
    $mapping['analytics_storage'] === 'granted',
    'GOOGLE_ANALYTICS_MAPPING'
);

assertTrue(
    $mapping['ad_storage'] === 'denied',
    'GOOGLE_AD_STORAGE_MAPPING'
);

assertTrue(
    $mapping['ad_user_data'] === 'denied',
    'GOOGLE_AD_USER_DATA_MAPPING'
);

assertTrue(
    $mapping['ad_personalization'] === 'denied',
    'GOOGLE_AD_PERSONALIZATION_MAPPING'
);

echo "P2_ACCEPTANCE=PASS\n";
