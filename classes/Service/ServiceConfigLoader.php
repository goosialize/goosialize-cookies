<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Service;

use Goosialize\Cookies\Consent\ConsentCategory;

final class ServiceConfigLoader
{
    /**
     * @param array<string, mixed> $config
     */
    public function load(array $config): ServiceRegistry
    {
        $registry = new ServiceRegistry();

        foreach ($config as $id => $definition) {
            if (!is_string($id)) {
                throw new \InvalidArgumentException(
                    'Service configuration IDs must be strings.'
                );
            }

            if (!is_array($definition)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Service configuration must be an array: %s',
                        $id
                    )
                );
            }

            $registry->register(
                $this->service(
                    $id,
                    $definition
                )
            );
        }

        return $registry;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function service(
        string $id,
        array $definition
    ): ServiceDefinition {
        $category =
            $this->category(
                $definition['category'] ?? null
            );

        return new ServiceDefinition(
            id: $id,
            name: $this->requiredString(
                $definition,
                'name'
            ),
            provider: $this->requiredString(
                $definition,
                'provider'
            ),
            category: $category,
            purpose: $this->requiredString(
                $definition,
                'purpose'
            ),
            cookies: $this->cookies(
                $definition['cookies'] ?? []
            ),
            storage: $this->storage(
                $definition['storage'] ?? []
            ),
            privacyUrl:
                $this->optionalString(
                    $definition,
                    'privacy_url'
                ),
            enabled:
                $this->enabled(
                    $definition['enabled'] ?? true
                )
        );
    }

    private function category(
        mixed $value
    ): ConsentCategory {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                'Service category must be a string.'
            );
        }

        $category =
            ConsentCategory::tryFrom($value);

        if ($category === null) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown service category: %s',
                    $value
                )
            );
        }

        return $category;
    }

    /**
     * @param mixed $value
     * @return list<CookieDefinition>
     */
    private function cookies(
        mixed $value
    ): array {
        if (!is_array($value)) {
            throw new \InvalidArgumentException(
                'Service cookies must be an array.'
            );
        }

        $cookies = [];

        foreach ($value as $definition) {
            if (!is_array($definition)) {
                throw new \InvalidArgumentException(
                    'Cookie configuration must be an array.'
                );
            }

            $cookies[] =
                new CookieDefinition(
                    name:
                        $this->requiredString(
                            $definition,
                            'name'
                        ),
                    purpose:
                        $this->requiredString(
                            $definition,
                            'purpose'
                        ),
                    duration:
                        $this->optionalString(
                            $definition,
                            'duration'
                        )
                );
        }

        return $cookies;
    }

    /**
     * @param mixed $value
     * @return list<StorageDefinition>
     */
    private function storage(
        mixed $value
    ): array {
        if (!is_array($value)) {
            throw new \InvalidArgumentException(
                'Service storage must be an array.'
            );
        }

        $storage = [];

        foreach ($value as $definition) {
            if (!is_array($definition)) {
                throw new \InvalidArgumentException(
                    'Storage configuration must be an array.'
                );
            }

            $typeValue =
                $definition['type'] ?? null;

            if (!is_string($typeValue)) {
                throw new \InvalidArgumentException(
                    'Storage type must be a string.'
                );
            }

            $type =
                StorageType::tryFrom(
                    $typeValue
                );

            if ($type === null) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unknown storage type: %s',
                        $typeValue
                    )
                );
            }

            $storage[] =
                new StorageDefinition(
                    type: $type,
                    key:
                        $this->requiredString(
                            $definition,
                            'key'
                        ),
                    purpose:
                        $this->requiredString(
                            $definition,
                            'purpose'
                        )
                );
        }

        return $storage;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function requiredString(
        array $source,
        string $key
    ): string {
        $value =
            $source[$key] ?? null;

        if (
            !is_string($value) ||
            trim($value) === ''
        ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Required string missing: %s',
                    $key
                )
            );
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function optionalString(
        array $source,
        string $key
    ): ?string {
        if (!array_key_exists($key, $source)) {
            return null;
        }

        $value = $source[$key];

        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Optional string must be a string: %s',
                    $key
                )
            );
        }

        return trim($value) === ''
            ? null
            : $value;
    }

    private function enabled(
        mixed $value
    ): bool {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException(
                'Service enabled flag must be boolean.'
            );
        }

        return $value;
    }
}
