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
use Goosialize\Cookies\Service\CookieDefinition;
use Goosialize\Cookies\Service\ServiceDefinition;
use Goosialize\Cookies\Service\ServiceRegistry;
use Goosialize\Cookies\Service\StorageDefinition;
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

$cookie = new CookieDefinition(
    '_ga',
    'Distinguishes visitors.',
    '2 years'
);

ok(
    $cookie->name === '_ga',
    'COOKIE_DEFINITION'
);

$storage = new StorageDefinition(
    StorageType::LocalStorage,
    'example_preferences',
    'Stores optional preferences.'
);

ok(
    $storage->type ===
        StorageType::LocalStorage,
    'STORAGE_DEFINITION'
);

$analytics = new ServiceDefinition(
    id: 'google.analytics',
    name: 'Google Analytics',
    provider: 'Google',
    category: ConsentCategory::Analytics,
    purpose: 'Website analytics.',
    cookies: [$cookie],
    storage: [],
    privacyUrl: 'https://policies.google.com/privacy',
    enabled: true
);

ok(
    $analytics->category ===
        ConsentCategory::Analytics,
    'SERVICE_ANALYTICS_CATEGORY'
);

$preferences = new ServiceDefinition(
    id: 'example.preferences',
    name: 'Example Preferences',
    provider: 'Example',
    category: ConsentCategory::Preferences,
    purpose: 'Remember visitor preferences.',
    cookies: [],
    storage: [$storage],
    privacyUrl: null,
    enabled: false
);

$registry = new ServiceRegistry();

$registry->register($analytics);
$registry->register($preferences);

ok(
    $registry->has('google.analytics'),
    'REGISTRY_HAS'
);

ok(
    $registry->get('google.analytics')
        === $analytics,
    'REGISTRY_GET'
);

ok(
    count($registry->all()) === 2,
    'REGISTRY_ALL'
);

ok(
    count($registry->enabled()) === 1,
    'REGISTRY_ENABLED_FILTER'
);

$duplicateRejected = false;

try {
    $registry->register($analytics);
} catch (LogicException) {
    $duplicateRejected = true;
}

ok(
    $duplicateRejected,
    'DUPLICATE_ID_REJECTED'
);

$necessaryRejected = false;

try {
    new ServiceDefinition(
        id: 'core.required',
        name: 'Required Service',
        provider: 'Example',
        category: ConsentCategory::Necessary,
        purpose: 'Required operation.'
    );
} catch (InvalidArgumentException) {
    $necessaryRejected = true;
}

ok(
    $necessaryRejected,
    'NECESSARY_CATEGORY_REJECTED'
);

$invalidIdRejected = false;

try {
    new ServiceDefinition(
        id: 'Bad Service ID!',
        name: 'Bad',
        provider: 'Example',
        category: ConsentCategory::Analytics,
        purpose: 'Invalid identifier.'
    );
} catch (InvalidArgumentException) {
    $invalidIdRejected = true;
}

ok(
    $invalidIdRejected,
    'INVALID_SERVICE_ID_REJECTED'
);

$invalidUrlRejected = false;

try {
    new ServiceDefinition(
        id: 'example.bad-url',
        name: 'Bad URL',
        provider: 'Example',
        category: ConsentCategory::Marketing,
        purpose: 'Marketing.',
        privacyUrl: 'not-a-url'
    );
} catch (InvalidArgumentException) {
    $invalidUrlRejected = true;
}

ok(
    $invalidUrlRejected,
    'INVALID_PRIVACY_URL_REJECTED'
);

$invalidCookieRejected = false;

try {
    new ServiceDefinition(
        id: 'example.bad-cookie',
        name: 'Bad Cookie',
        provider: 'Example',
        category: ConsentCategory::Analytics,
        purpose: 'Analytics.',
        cookies: ['_ga']
    );
} catch (InvalidArgumentException) {
    $invalidCookieRejected = true;
}

ok(
    $invalidCookieRejected,
    'INVALID_COOKIE_OBJECT_REJECTED'
);

$invalidStorageRejected = false;

try {
    new ServiceDefinition(
        id: 'example.bad-storage',
        name: 'Bad Storage',
        provider: 'Example',
        category: ConsentCategory::Preferences,
        purpose: 'Preferences.',
        storage: ['local_storage']
    );
} catch (InvalidArgumentException) {
    $invalidStorageRejected = true;
}

ok(
    $invalidStorageRejected,
    'INVALID_STORAGE_OBJECT_REJECTED'
);

$emptyCookieRejected = false;

try {
    new CookieDefinition(
        '',
        'Purpose.'
    );
} catch (InvalidArgumentException) {
    $emptyCookieRejected = true;
}

ok(
    $emptyCookieRejected,
    'EMPTY_COOKIE_NAME_REJECTED'
);

$emptyStorageRejected = false;

try {
    new StorageDefinition(
        StorageType::SessionStorage,
        '',
        'Purpose.'
    );
} catch (InvalidArgumentException) {
    $emptyStorageRejected = true;
}

ok(
    $emptyStorageRejected,
    'EMPTY_STORAGE_KEY_REJECTED'
);

echo "P6_REGISTRY_ACCEPTANCE=PASS\n";
