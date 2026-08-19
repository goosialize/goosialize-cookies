<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Privacy;

/**
 * Common vocabulary for the Goosialize ecosystem.
 *
 * Cookie consent remains owned by Goosialize Cookies.
 * Form/privacy purposes remain owned by the plugin collecting the data.
 */
final readonly class PrivacyPurpose
{
    public function __construct(
        public string $id,
        public string $owner,
        public string $purpose,
        public int $version = 1,
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('Privacy purpose ID cannot be empty.');
        }

        if ($owner === '') {
            throw new \InvalidArgumentException('Privacy purpose owner cannot be empty.');
        }

        if ($purpose === '') {
            throw new \InvalidArgumentException('Privacy purpose description cannot be empty.');
        }

        if ($version < 1) {
            throw new \InvalidArgumentException('Privacy purpose version must be >= 1.');
        }
    }
}
