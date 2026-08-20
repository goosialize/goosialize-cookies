<?php

declare(strict_types=1);

require '/app/www/public/vendor/autoload.php';

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

use Goosialize\Cookies\Service\ServiceConfigWriter;

final class FakeConfig extends \Grav\Common\Config\Config
{
    public array $writes = [];
    public int $saveCount = 0;

    public function __construct()
    {
    }

    public function set(
        $name,
        $value,
        $separator = null
    ) {
        $this->writes[(string) $name] =
            $value;

        return $this;
    }

    public function save()
    {
        $this->saveCount++;

        return true;
    }
}

function ok(
    bool $condition,
    string $label
): void {
    if (!$condition) {
        throw new RuntimeException(
            "FAIL={$label}"
        );
    }

    echo "{$label}=PASS\n";
}

$valid = [
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
        ],
        'storage' => [],
    ],

    'site.preferences' => [
        'name' => 'Site Preferences',
        'provider' => 'Site',
        'category' => 'preferences',
        'purpose' =>
            'Remember optional preferences.',
        'enabled' => false,
        'cookies' => [],
        'storage' => [
            [
                'type' => 'local_storage',
                'key' => 'site_preferences',
                'purpose' =>
                    'Stores optional preferences.',
            ],
        ],
    ],
];

$config = new FakeConfig();

(new ServiceConfigWriter(
    $config
))->replace($valid);

ok(
    isset(
        $config->writes[
            'plugins.goosialize-cookies.services'
        ]
    ),
    'VALID_WRITE_OCCURRED'
);

ok(
    $config->writes[
        'plugins.goosialize-cookies.services'
    ] === $valid,
    'VALID_WRITE_EXACT'
);

ok(
    $config->saveCount === 1,
    'VALID_SAVE_ONCE'
);

$invalid = [
    'bad.required' => [
        'name' => 'Bad Necessary',
        'provider' => 'Example',
        'category' => 'necessary',
        'purpose' => 'Should be rejected.',
        'enabled' => true,
    ],
];

$invalidConfig =
    new FakeConfig();

$invalidRejected = false;

try {
    (new ServiceConfigWriter(
        $invalidConfig
    ))->replace($invalid);
} catch (InvalidArgumentException) {
    $invalidRejected = true;
}

ok(
    $invalidRejected,
    'INVALID_CONFIG_REJECTED'
);

ok(
    $invalidConfig->writes === [],
    'INVALID_CONFIG_NO_WRITE'
);

ok(
    $invalidConfig->saveCount === 0,
    'INVALID_CONFIG_NO_SAVE'
);

$badStorage = [
    'bad.storage' => [
        'name' => 'Bad Storage',
        'provider' => 'Example',
        'category' => 'preferences',
        'purpose' => 'Invalid storage.',
        'storage' => [
            [
                'type' => 'unknown_storage',
                'key' => 'x',
                'purpose' => 'Invalid.',
            ],
        ],
    ],
];

$storageConfig =
    new FakeConfig();

$storageRejected = false;

try {
    (new ServiceConfigWriter(
        $storageConfig
    ))->replace($badStorage);
} catch (InvalidArgumentException) {
    $storageRejected = true;
}

ok(
    $storageRejected,
    'INVALID_STORAGE_REJECTED'
);

ok(
    $storageConfig->writes === [],
    'INVALID_STORAGE_NO_WRITE'
);

ok(
    $storageConfig->saveCount === 0,
    'INVALID_STORAGE_NO_SAVE'
);

echo "P7_SERVICE_WRITER_ACCEPTANCE=PASS\n";
