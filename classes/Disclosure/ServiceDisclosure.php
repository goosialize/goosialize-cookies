<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Disclosure;

use Goosialize\Cookies\Service\ServiceDefinition;

final class ServiceDisclosure
{
    /**
     * @param list<ServiceDefinition> $services
     * @return array<string, list<array<string, mixed>>>
     */
    public function groupByCategory(
        array $services
    ): array {
        $grouped = [
            'necessary' => [],
            'preferences' => [],
            'analytics' => [],
            'marketing' => [],
        ];

        foreach ($services as $service) {
            if (!$service->enabled) {
                continue;
            }

            $category =
                $service->category->value;

            if (!isset($grouped[$category])) {
                continue;
            }

            $grouped[$category][] = [
                'id' =>
                    $service->id,

                'name' =>
                    $service->name,

                'provider' =>
                    $service->provider,

                'purpose' =>
                    $service->purpose,

                'privacy_url' =>
                    $service->privacyUrl,

                'cookies' =>
                    array_map(
                        static fn ($cookie): array => [
                            'name' =>
                                $cookie->name,

                            'purpose' =>
                                $cookie->purpose,

                            'duration' =>
                                $cookie->duration,
                        ],
                        $service->cookies
                    ),

                'storage' =>
                    array_map(
                        static fn ($storage): array => [
                            'type' =>
                                $storage->type->value,

                            'key' =>
                                $storage->key,

                            'purpose' =>
                                $storage->purpose,
                        ],
                        $service->storage
                    ),
            ];
        }

        return $grouped;
    }
}
