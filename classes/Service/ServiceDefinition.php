<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Service;

use Goosialize\Cookies\Consent\ConsentCategory;

final readonly class ServiceDefinition
{
    /**
     * @param list<string> $cookieNames
     * @param list<string> $storageTypes
     */
    public function __construct(
        public string $id,
        public string $name,
        public ConsentCategory $category,
        public string $provider,
        public string $purpose,
        public array $cookieNames = [],
        public array $storageTypes = [],
        public ?string $privacyUrl = null,
        public bool $enabled = true,
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('Service ID cannot be empty.');
        }

        if ($name === '') {
            throw new \InvalidArgumentException('Service name cannot be empty.');
        }
    }
}
