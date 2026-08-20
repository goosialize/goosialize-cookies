<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Service;

final readonly class StorageDefinition
{
    public function __construct(
        public StorageType $type,
        public string $key,
        public string $purpose,
    ) {
        if (trim($key) === '') {
            throw new \InvalidArgumentException(
                'Storage key cannot be empty.'
            );
        }

        if (trim($purpose) === '') {
            throw new \InvalidArgumentException(
                'Storage purpose cannot be empty.'
            );
        }
    }
}
