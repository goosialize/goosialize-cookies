<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Service;

final readonly class CookieDefinition
{
    public function __construct(
        public string $name,
        public string $purpose,
        public ?string $duration = null,
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException(
                'Cookie name cannot be empty.'
            );
        }

        if (trim($purpose) === '') {
            throw new \InvalidArgumentException(
                'Cookie purpose cannot be empty.'
            );
        }
    }
}
