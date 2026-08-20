<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Service;

use Goosialize\Cookies\Consent\ConsentCategory;

final readonly class ServiceDefinition
{
    /**
     * @param list<CookieDefinition> $cookies
     * @param list<StorageDefinition> $storage
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $provider,
        public ConsentCategory $category,
        public string $purpose,
        public array $cookies = [],
        public array $storage = [],
        public ?string $privacyUrl = null,
        public bool $enabled = true,
    ) {
        if (trim($id) === '') {
            throw new \InvalidArgumentException(
                'Service ID cannot be empty.'
            );
        }

        if (
            preg_match(
                '/^[a-z0-9][a-z0-9._-]*$/',
                $id
            ) !== 1
        ) {
            throw new \InvalidArgumentException(
                'Service ID contains invalid characters.'
            );
        }

        if (trim($name) === '') {
            throw new \InvalidArgumentException(
                'Service name cannot be empty.'
            );
        }

        if (trim($provider) === '') {
            throw new \InvalidArgumentException(
                'Service provider cannot be empty.'
            );
        }

        if (trim($purpose) === '') {
            throw new \InvalidArgumentException(
                'Service purpose cannot be empty.'
            );
        }

        if ($category === ConsentCategory::Necessary) {
            throw new \InvalidArgumentException(
                'Consent-managed services cannot use the necessary category.'
            );
        }

        foreach ($cookies as $cookie) {
            if (!$cookie instanceof CookieDefinition) {
                throw new \InvalidArgumentException(
                    'Service cookies must contain CookieDefinition objects.'
                );
            }
        }

        foreach ($storage as $item) {
            if (!$item instanceof StorageDefinition) {
                throw new \InvalidArgumentException(
                    'Service storage must contain StorageDefinition objects.'
                );
            }
        }

        if (
            $privacyUrl !== null &&
            trim($privacyUrl) !== '' &&
            filter_var(
                $privacyUrl,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            throw new \InvalidArgumentException(
                'Service privacy URL must be valid.'
            );
        }
    }
}
