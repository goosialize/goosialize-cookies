<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Service;

final class ServiceRegistry
{
    /**
     * @var array<string, ServiceDefinition>
     */
    private array $services = [];

    public function register(
        ServiceDefinition $service
    ): void {
        if (isset($this->services[$service->id])) {
            throw new \LogicException(
                sprintf(
                    'Service already registered: %s',
                    $service->id
                )
            );
        }

        $this->services[$service->id] =
            $service;
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function get(
        string $id
    ): ?ServiceDefinition {
        return $this->services[$id] ?? null;
    }

    /**
     * @return list<ServiceDefinition>
     */
    public function all(): array
    {
        return array_values(
            $this->services
        );
    }

    /**
     * @return list<ServiceDefinition>
     */
    public function enabled(): array
    {
        return array_values(
            array_filter(
                $this->services,
                static fn (
                    ServiceDefinition $service
                ): bool => $service->enabled
            )
        );
    }
}
