<?php

declare(strict_types=1);

spl_autoload_register(
    static function (string $class): void {
        $prefix = 'Goosialize\\Cookies\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr(
            $class,
            strlen($prefix)
        );

        $file = dirname(__DIR__)
            . '/classes/'
            . str_replace('\\', '/', $relative)
            . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
);

use Goosialize\Cookies\Consent\ConsentCategory;
use Goosialize\Cookies\Service\ServiceConfigLoader;
use Goosialize\Cookies\Service\StorageType;

function ok(bool $condition, string $label): void
{
    if (!$condition) {
        throw new RuntimeException(
            "FAIL=$label"
        );
    }

    echo "$label=PASS\n";
}

$loader = new ServiceConfigLoader();

$registry = $loader->load([
    'google.analytics' => [
        'name' => 'Google Analytics',
        'provider' => 'Google',
        'category' => 'analytics',
        'purpose' => 'Website analytics.',
        'privacy_url' =>
            'https://policies.google.com/privacy',
        'enabled' => true,
        'cookies' => [
            [
                'name' => '_ga',
                'purpose' =>
                    'Distinguishes visitors.',
                'duration' => '2 years',
            ],
            [
                'name' => '_ga_*',
                'purpose' =>
                    'Maintains session state.',
                'duration' => '2 years',
            ],
        ],
        'storage' => [],
    ],

    'meta.pixel' => [
        'name' => 'Meta Pixel',
        'provider' => 'Meta',
        'category' => 'marketing',
        'purpose' =>
            'Advertising measurement.',
        'privacy_url' =>
            'https://www.facebook.com/privacy/policy/',
        'enabled' => false,
        'cookies' => [
            [
                'name' => '_fbp',
                'purpose' =>
                    'Advertising measurement.',
                'duration' => '3 months',
            ],
        ],
        'storage' => [],
    ],

    'example.preferences' => [
        'name' => 'Site Preferences',
        'provider' => 'Site',
        'category' => 'preferences',
        'purpose' =>
            'Remember optional visitor preferences.',
        'enabled' => true,
        'cookies' => [],
        'storage' => [
            [
                'type' => 'local_storage',
                'key' => 'site_preferences',
                'purpose' =>
                    'Stores optional UI preferences.',
            ],
        ],
    ],
]);

ok(
    count($registry->all()) === 3,
    'CONFIG_REGISTRY_COUNT'
);

$ga = $registry->get(
    'google.analytics'
);

ok(
    $ga !== null,
    'GA_SERVICE_LOADED'
);

ok(
    $ga?->category ===
        ConsentCategory::Analytics,
    'GA_CATEGORY'
);

ok(
    count($ga?->cookies ?? []) === 2,
    'GA_COOKIES'
);

$meta = $registry->get(
    'meta.pixel'
);

ok(
    $meta !== null &&
    $meta->enabled === false,
    'META_DISABLED_STATE'
);

$preferences = $registry->get(
    'example.preferences'
);

ok(
    $preferences !== null,
    'PREFERENCES_SERVICE_LOADED'
);

ok(
    $preferences?->storage[0]->type ===
        StorageType::LocalStorage,
    'PREFERENCES_STORAGE_TYPE'
);

ok(
    count($registry->enabled()) === 2,
    'ENABLED_SERVICE_FILTER'
);

$unknownCategoryRejected = false;

try {
    $loader->load([
        'bad.category' => [
            'name' => 'Bad Category',
            'provider' => 'Example',
            'category' => 'tracking',
            'purpose' => 'Invalid.',
        ],
    ]);
} catch (InvalidArgumentException) {
    $unknownCategoryRejected = true;
}

ok(
    $unknownCategoryRejected,
    'UNKNOWN_CATEGORY_REJECTED'
);

$necessaryRejected = false;

try {
    $loader->load([
        'bad.necessary' => [
            'name' => 'Bad Necessary',
            'provider' => 'Example',
            'category' => 'necessary',
            'purpose' => 'Invalid.',
        ],
    ]);
} catch (InvalidArgumentException) {
    $necessaryRejected = true;
}

ok(
    $necessaryRejected,
    'CONFIG_NECESSARY_REJECTED'
);

$unknownStorageRejected = false;

try {
    $loader->load([
        'bad.storage' => [
            'name' => 'Bad Storage',
            'provider' => 'Example',
            'category' => 'preferences',
            'purpose' => 'Invalid.',
            'storage' => [
                [
                    'type' => 'magic_storage',
                    'key' => 'x',
                    'purpose' => 'Invalid.',
                ],
            ],
        ],
    ]);
} catch (InvalidArgumentException) {
    $unknownStorageRejected = true;
}

ok(
    $unknownStorageRejected,
    'UNKNOWN_STORAGE_REJECTED'
);

$nonBooleanEnabledRejected = false;

try {
    $loader->load([
        'bad.enabled' => [
            'name' => 'Bad Enabled',
            'provider' => 'Example',
            'category' => 'analytics',
            'purpose' => 'Invalid.',
            'enabled' => 1,
        ],
    ]);
} catch (InvalidArgumentException) {
    $nonBooleanEnabledRejected = true;
}

ok(
    $nonBooleanEnabledRejected,
    'NON_BOOLEAN_ENABLED_REJECTED'
);

echo "P6_CONFIG_LOADER_ACCEPTANCE=PASS\n";
