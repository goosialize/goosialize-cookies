<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Admin;

use Grav\Plugin\Api\Controllers\AbstractApiController;
use Grav\Plugin\Api\Response\ApiResponse;
use Goosialize\Cookies\Service\ServiceConfigLoader;
use Goosialize\Cookies\Service\ServiceConfigWriter;
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

        $services =
            $this->serializeServices(
                $registry->all()
            );

        return ApiResponse::ok([
            'services' => $services,
            'count' => count($services),
        ]);
    }
    /**
     * @param list<\Goosialize\Cookies\Service\ServiceDefinition> $items
     * @return list<array<string, mixed>>
     */
    private function serializeServices(
        array $items
    ): array {
        $services = [];

        foreach ($items as $service) {
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

        return $services;
    }


    public function replace(
        ServerRequestInterface $request
    ): ResponseInterface {
        $this->requirePermission(
            $request,
            'api.goosialize.cookies.services'
        );

        $body =
            $this->getRequestBody($request);

        $services =
            $body['services'] ?? null;

        if (!is_array($services)) {
            throw new \Grav\Plugin\Api\Exceptions\ValidationException(
                [
                    [
                        'field' => 'services',
                        'message' =>
                            'Services must be an object.',
                    ],
                ]
            );
        }

        try {
            (new ServiceConfigWriter(
                $this->config
            ))->replace($services);
        } catch (\InvalidArgumentException|\LogicException $error) {
            throw new \Grav\Plugin\Api\Exceptions\ValidationException(
                [
                    [
                        'field' => 'services',
                        'message' =>
                            $error->getMessage(),
                    ],
                ]
            );
        }

        $registry =
            (new ServiceConfigLoader())
                ->load($services);

        return ApiResponse::ok([
            'services' =>
                $this->serializeServices(
                    $registry->all()
                ),
            'count' =>
                count($registry->all()),
        ]);
    }

}
