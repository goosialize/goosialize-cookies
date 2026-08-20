<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Admin;

use Grav\Plugin\Api\Controllers\AbstractApiController;
use Grav\Plugin\Api\Response\ApiResponse;
use Goosialize\Cookies\Service\ServiceConfigLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ServicesApiController extends AbstractApiController
{
    public function index(
        ServerRequestInterface $request
    ): ResponseInterface {
        $this->requirePermission(
            $request,
            'api.goosialize.cookies.read'
        );

        $config = (array) $this->config->get(
            'plugins.goosialize-cookies.services',
            []
        );

        $registry = (new ServiceConfigLoader())
            ->load($config);

        $services = [];

        foreach ($registry->all() as $service) {
            $services[] = [
                'id' => $service->id,
                'name' => $service->name,
                'provider' => $service->provider,
                'category' =>
                    $service->category->value,
                'purpose' => $service->purpose,
                'privacy_url' =>
                    $service->privacyUrl,
                'enabled' => $service->enabled,

                'cookies' => array_map(
                    static fn ($cookie): array => [
                        'name' => $cookie->name,
                        'purpose' =>
                            $cookie->purpose,
                        'duration' =>
                            $cookie->duration,
                    ],
                    $service->cookies
                ),

                'storage' => array_map(
                    static fn ($storage): array => [
                        'type' =>
                            $storage->type->value,
                        'key' => $storage->key,
                        'purpose' =>
                            $storage->purpose,
                    ],
                    $service->storage
                ),
            ];
        }

        return ApiResponse::success([
            'services' => $services,
            'count' => count($services),
        ]);
    }
}
