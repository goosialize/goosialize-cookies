<?php

declare(strict_types=1);

$root = dirname(__DIR__);

require_once $root
    . '/classes/Admin/AvailableAddonRegistry.php';

$fail = static function (string $label): void {
    fwrite(
        STDERR,
        $label . '=FAIL' . PHP_EOL
    );
    exit(1);
};

$pass = static function (string $label): void {
    echo $label . '=PASS' . PHP_EOL;
};

$registry =
    \Goosialize\Cookies\Admin\AvailableAddonRegistry::all();

if (count($registry) !== 1) {
    $fail('REGISTRY_COUNT');
}

$addon = $registry[0];

$expected = [
    'slug' =>
        'goosialize-cookies-appearance',
    'name' =>
        'Goosialize Cookies Appearance',
    'kind' =>
        'Paid commercial add-on',
    'minimum_free_version' =>
        '1.1.0',
    'description' =>
        'Advanced presentation controls for the '
        . 'Goosialize Cookies consent interface.',
];

if ($addon !== $expected) {
    $fail('REGISTRY_CONTENT');
}

$pass('REGISTRY_CONTENT');

$plugin =
    file_get_contents(
        $root . '/goosialize-cookies.php'
    );

if (is_string($plugin) === false) {
    $fail('PLUGIN_READ');
}

$required = [
    "'onApiBlueprintResolved' => "
        . "['onApiBlueprintResolved', 0]",
    'AvailableAddonRegistry::all()',
    "'context'] ?? null) === 'plugin-page'",
    "'plugin'] ?? null)",
    "=== 'goosialize-cookies'",
    "'page_id'] ?? null)",
    "'type' => 'section'",
    "'title' => 'Available Add-ons'",
    "'type' => 'display'",
    "'markdown' => true",
    "'plugin://'",
    "findResource(",
    "'Installed'",
    "'Not installed'",
];

foreach ($required as $token) {
    if (
        str_contains(
            $plugin,
            $token
        ) === false
    ) {
        fwrite(
            STDERR,
            'MISSING_TOKEN='
                . $token
                . PHP_EOL
        );
        $fail('PLUGIN_CONTRACT');
    }
}

$pass('PLUGIN_CONTRACT');

$registrySource =
    file_get_contents(
        $root
        . '/classes/Admin/'
        . 'AvailableAddonRegistry.php'
    );

if (is_string($registrySource) === false) {
    $fail('REGISTRY_READ');
}

$forbidden = [
    'license_key',
    'activation_id',
    'commerce_client',
    'localStorage',
    'curl_',
];

foreach ($forbidden as $token) {
    if (
        str_contains(
            $registrySource,
            $token
        )
    ) {
        $fail('REGISTRY_BOUNDARY');
    }
}

$pass('REGISTRY_BOUNDARY');

if (
    str_contains(
        $plugin,
        '<script'
    )
    || str_contains(
        $plugin,
        '<style'
    )
) {
    $fail('CUSTOM_UI_BOUNDARY');
}

$pass('CUSTOM_UI_BOUNDARY');

$self = file_get_contents(__FILE__);

if (
    is_string($self)
    && preg_match(
        '/\bassert\s*\(/',
        $self
    ) === 1
) {
    $fail('ASSERTION_FREE');
}

$pass('ASSERTION_FREE');

echo 'P25_C7_D0_B1_R2_SOURCE=PASS'
    . PHP_EOL;
