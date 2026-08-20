<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Service;

use Grav\Common\Config\Config;

final class ServiceConfigWriter
{
    public function __construct(
        private readonly Config $config,
    ) {
    }

    /**
     * @param array<string, mixed> $services
     */
    public function replace(
        array $services
    ): void {
        /*
         * Validate the complete proposed registry before any write.
         * No invalid definition is allowed to reach persisted config.
         */
        (new ServiceConfigLoader())
            ->load($services);

        $this->config->set(
            'plugins.goosialize-cookies.services',
            $services
        );

        $this->config->save();
    }
}
