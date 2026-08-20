<?php

declare(strict_types=1);

require_once __DIR__
    . '/../classes/Consent/ConsentCategory.php';

require_once __DIR__
    . '/../classes/Consent/ConsentSelection.php';

require_once __DIR__
    . '/../classes/Consent/ConsentVersion.php';

require_once __DIR__
    . '/../classes/Consent/ConsentValidator.php';

require_once __DIR__
    . '/../classes/Consent/ConsentLifecycleStatus.php';

require_once __DIR__
    . '/../classes/Consent/ConsentLifecycleEvaluator.php';

use Goosialize\Cookies\Consent\ConsentLifecycleEvaluator;
use Goosialize\Cookies\Consent\ConsentLifecycleStatus;
use Goosialize\Cookies\Consent\ConsentVersion;

function check(
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

$now =
    new DateTimeImmutable(
        '2026-08-20T12:00:00+00:00'
    );

$evaluator =
    new ConsentLifecycleEvaluator(
        new ConsentVersion(2),
        180
    );

$payload = static function (
    int $version,
    string $timestamp
): string {
    return json_encode(
        [
            'version' => $version,
            'timestamp' => $timestamp,
            'categories' => [
                'necessary' => true,
                'preferences' => false,
                'analytics' => true,
                'marketing' => false,
            ],
        ],
        JSON_THROW_ON_ERROR
    );
};

check(
    $evaluator->evaluate(
        null,
        $now
    ) === ConsentLifecycleStatus::Missing,
    'MISSING'
);

check(
    $evaluator->evaluate(
        '{bad-json',
        $now
    ) === ConsentLifecycleStatus::Malformed,
    'MALFORMED'
);

check(
    $evaluator->evaluate(
        $payload(
            1,
            '2026-08-20T11:00:00+00:00'
        ),
        $now
    ) === ConsentLifecycleStatus::VersionMismatch,
    'VERSION_MISMATCH'
);

check(
    $evaluator->evaluate(
        $payload(
            2,
            '2026-01-01T00:00:00+00:00'
        ),
        $now
    ) === ConsentLifecycleStatus::Expired,
    'EXPIRED'
);

check(
    $evaluator->evaluate(
        $payload(
            2,
            '2026-08-21T00:00:00+00:00'
        ),
        $now
    ) === ConsentLifecycleStatus::FutureTimestamp,
    'FUTURE_TIMESTAMP'
);

check(
    $evaluator->evaluate(
        $payload(
            2,
            '2026-08-20T11:00:00+00:00'
        ),
        $now
    ) === ConsentLifecycleStatus::Valid,
    'VALID'
);

echo "P11_PHP_LIFECYCLE_ACCEPTANCE=PASS\n";
