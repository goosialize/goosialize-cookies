<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Events\PermissionsRegisterEvent;
use Grav\Framework\Acl\PermissionsReader;

final class GoosializeCookiesPlugin extends Plugin
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onOutputGenerated' => ['onOutputGenerated', 0],
            'onApiRegisterRoutes' => ['onApiRegisterRoutes', 0],
            'onApiSidebarItems' => ['onApiSidebarItems', 0],
            'onApiPluginPageInfo' => ['onApiPluginPageInfo', 0],
            PermissionsRegisterEvent::class => ['onRegisterPermissions', 1000],
        ];
    }

    public function autoload(): \Composer\Autoload\ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    public function onPluginsInitialized(): void
    {
        if (!$this->isEnabled()) {
            return;
        }
    }

    public function onTwigSiteVariables(): void
    {
        if (
            !$this->isEnabled() ||
            $this->isAdmin()
        ) {
            return;
        }

        $assets = $this->grav['assets'];

        $assets->addCss(
            'plugin://goosialize-cookies/assets/css/consent.css',
            [
                'priority' => 100,
            ]
        );

        $assets->addJs(
            'plugin://goosialize-cookies/assets/js/consent.js',
            [
                'group' => 'bottom',
                'priority' => 100,
            ]
        );

        $assets->addJs(
            'plugin://goosialize-cookies/assets/js/bootstrap.js',
            [
                'group' => 'bottom',
                'priority' => 90,
            ]
        );

        $assets->addJs(
            'plugin://goosialize-cookies/assets/js/blocker.js',
            [
                'group' => 'bottom',
                'priority' => 85,
            ]
        );

        $assets->addJs(
            'plugin://goosialize-cookies/assets/js/ui.js',
            [
                'group' => 'bottom',
                'priority' => 80,
            ]
        );
    }

    public function onOutputGenerated(): void
    {
        if (
            !$this->isEnabled() ||
            $this->isAdmin()
        ) {
            return;
        }

        $output = (string) $this->grav->output;

        if (
            str_contains(
                $output,
                'data-goosialize-consent-root'
            )
        ) {
            return;
        }

        $twig = $this->grav['twig'];

        $markup = $twig->processTemplate(
            'partials/goosialize-cookies-ui.html.twig'
        );

        if ($markup === '') {
            return;
        }

        if (
            preg_match(
                '/<\/body\s*>/i',
                $output
            ) === 1
        ) {
            $updated = preg_replace(
                '/<\/body\s*>/i',
                $markup . "\n</body>",
                $output,
                1
            );

            if (is_string($updated)) {
                $this->grav->output = $updated;
            }

            return;
        }

        $this->grav->output =
            $output . $markup;
    }

    public function onRegisterPermissions(
        PermissionsRegisterEvent $event
    ): void {
        $actions = PermissionsReader::fromYaml(
            "plugin://{$this->name}/permissions.yaml"
        );

        $event->permissions->addActions(
            $actions
        );
    }

    public function onApiSidebarItems(
        \RocketTheme\Toolbox\Event\Event $event
    ): void {
        if (!$this->isEnabled()) {
            return;
        }

        $user = $event['user'] ?? null;

        if (!$this->admin2Allowed($user)) {
            return;
        }

        $items = $event['items'] ?? [];

        if (!is_array($items)) {
            $items = [];
        }

        $items[] = [
            'id' => 'goosialize-cookies',
            'plugin' => 'goosialize-cookies',
            'label' => 'Goosialize Cookies',
            'icon' => 'fa-cookie-bite',
            'route' => '/plugin/goosialize-cookies',
            'priority' => 19,
            'badge' => null,
            'authorize' =>
                'api.goosialize.cookies.read',
        ];

        $event['items'] = $items;
    }

    public function onApiPluginPageInfo(
        \RocketTheme\Toolbox\Event\Event $event
    ): void {
        if (
            ($event['plugin'] ?? null)
                !== 'goosialize-cookies'
            || !$this->isEnabled()
            || !$this->admin2Allowed(
                $event['user'] ?? null
            )
        ) {
            return;
        }

        $event['definition'] = [
            'id' => 'goosialize-cookies',
            'plugin' => 'goosialize-cookies',
            'title' => 'Goosialize Cookies',
            'icon' => 'fa-cookie-bite',
            'page_type' => 'component',
        ];
    }

    private function admin2Allowed(
        mixed $user
    ): bool {
        if (!is_object($user)) {
            return false;
        }

        try {
            if (method_exists($user, 'get')) {
                if (
                    (bool) $user->get(
                        'access.admin.super'
                    )
                    || (bool) $user->get(
                        'access.api.super'
                    )
                ) {
                    return true;
                }

                if (
                    !(bool) $user->get(
                        'access.api.access'
                    )
                ) {
                    return false;
                }

                return (bool) $user->get(
                    'access.api.goosialize.cookies.read'
                );
            }

            if (method_exists($user, 'authorize')) {
                if (
                    (bool) $user->authorize(
                        'api.super'
                    )
                ) {
                    return true;
                }

                if (
                    !(bool) $user->authorize(
                        'api.access'
                    )
                ) {
                    return false;
                }

                return (bool) $user->authorize(
                    'api.goosialize.cookies.read'
                );
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    public function onApiRegisterRoutes(
        \RocketTheme\Toolbox\Event\Event $event
    ): void {
        $routes = $event['routes'] ?? null;

        if (
            !$routes instanceof
            \Grav\Plugin\Api\ApiRouteCollector
        ) {
            return;
        }

        $routes->get(
            '/goosialize-cookies/services',
            [
                \Goosialize\Cookies\Admin\ServicesApiController::class,
                'index',
            ]
        );
    }

    private function isEnabled(): bool
    {
        return (bool) $this->config->get(
            'plugins.goosialize-cookies.enabled',
            true
        );
    }
}
