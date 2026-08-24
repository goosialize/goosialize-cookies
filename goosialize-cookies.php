<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Events\PermissionsRegisterEvent;
use Grav\Framework\Acl\PermissionsReader;
use Goosialize\Cookies\Admin\AvailableAddonRegistry;
use Goosialize\Cookies\Disclosure\ServiceDisclosure;
use Goosialize\Cookies\Presentation\PresentationBridge;
use Goosialize\Cookies\Service\ServiceConfigLoader;

final class GoosializeCookiesPlugin extends Plugin
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onOutputGenerated' => ['onOutputGenerated', 0],
            'onApiRegisterRoutes' => ['onApiRegisterRoutes', 0],
            'onApiSidebarItems' => ['onApiSidebarItems', 0],
            'onApiPluginPageInfo' => ['onApiPluginPageInfo', 0],
            'onApiBlueprintResolved' => ['onApiBlueprintResolved', 0],
            PermissionsRegisterEvent::class => ['onRegisterPermissions', 1000],
        ];
    }

    public function autoload(): ClassLoader
    {
        $loader = new ClassLoader();

        $loader->addPsr4(
            'Goosialize\\Cookies\\',
            __DIR__ . '/classes'
        );

        $loader->register();

        return $loader;
    }

    public function onPluginsInitialized(): void
    {
        if (!$this->isEnabled()) {
            return;
        }
    }

    public function onTwigTemplatePaths(): void
    {
        $twig = $this->grav['twig'];

        $path = __DIR__ . '/templates';

        if (
            !in_array(
                $path,
                $twig->twig_paths,
                true
            )
        ) {
            $twig->twig_paths[] = $path;
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

        $config = $this->grav['config'];

        $provider = $this->grav[
            'goosialize.cookies.presentation_provider'
        ] ?? null;

        $presentation = (new PresentationBridge())
            ->resolve($provider);

        $this->grav['twig']->twig_vars[
            'goosialize_cookies_presentation'
        ] = $presentation->toArray();

        $serviceConfig = (array) $config->get(
            'plugins.goosialize-cookies.services',
            []
        );

        $registry =
            (new ServiceConfigLoader())
                ->load($serviceConfig);

        $this->grav['twig']->twig_vars[
            'goosialize_cookies_services'
        ] = (new ServiceDisclosure())
            ->groupByCategory(
                $registry->enabled()
            );

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
            'plugin://goosialize-cookies/assets/js/consumer.js',
            [
                'group' => 'bottom',
                'priority' => 98,
            ]
        );

        if ($this->isGoogleConsentModeEnabled()) {
            $assets->addJs(
                'plugin://goosialize-cookies/assets/js/google-consent-mode.js',
                [
                    'group' => 'bottom',
                    'priority' => 95,
                ]
            );
        }

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

    public function onApiBlueprintResolved(
        \RocketTheme\Toolbox\Event\Event $event
    ): void {
        $matches =
            ($event['context'] ?? null) === 'plugin-page'
            && ($event['plugin'] ?? null)
                === 'goosialize-cookies'
            && ($event['page_id'] ?? null)
                === 'goosialize-cookies'
            && $this->isEnabled()
            && $this->admin2Allowed(
                $event['user'] ?? null
            );

        if ($matches === false) {
            return;
        }

        $fields =
            is_array($event['fields'] ?? null)
                ? $event['fields']
                : [];

        $addonFields = [];

        foreach (
            AvailableAddonRegistry::all()
            as $addon
        ) {
            $resource =
                $this->grav['locator']->findResource(
                    'plugin://' . $addon['slug']
                );

            $installed =
                is_string($resource)
                && trim($resource) !== '';

            $content = [
                '**' . $addon['name'] . '**',
                '',
                $addon['description'],
                '',
                'Type: ' . $addon['kind'],
                '',
                'Requires Goosialize Cookies >= '
                    . $addon['minimum_free_version'],
                '',
                'Status: **'
                    . (
                        $installed
                            ? 'Installed'
                            : 'Not installed'
                    )
                    . '**',
            ];

            if ($installed) {
                $content[] = '';
                $content[] =
                    'Manage this add-on from its '
                    . 'Goosialize Cookies Appearance '
                    . 'Admin2 sidebar entry.';
            }

            $addonFields[] = [
                'name' =>
                    'available_addons.'
                    . $addon['slug'],
                'type' => 'display',
                'markdown' => true,
                'content' => implode(
                    "\n",
                    $content
                ),
            ];
        }

        $fields[] = [
            'name' => 'available_addons',
            'type' => 'section',
            'title' => 'Available Add-ons',
            'text' =>
                'Extend Goosialize Cookies with '
                . 'compatible add-ons.',
            'fields' => $addonFields,
        ];

        $event['fields'] = $fields;
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

        $routes->patch(
            '/goosialize-cookies/services',
            [
                \Goosialize\Cookies\Admin\ServicesApiController::class,
                'replace',
            ]
        );
    }

    private function isGoogleConsentModeEnabled(): bool
    {
        $enabled = $this->grav['config']->get(
            'plugins.goosialize-cookies.integrations.google.enabled',
            false
        );

        $mode = $this->grav['config']->get(
            'plugins.goosialize-cookies.integrations.google.consent_mode',
            'basic'
        );

        return $enabled === true
            && $mode === 'basic';
    }


    private function isEnabled(): bool
    {
        return (bool) $this->config->get(
            'plugins.goosialize-cookies.enabled',
            true
        );
    }
}
