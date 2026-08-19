<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Grav\Common\Plugin;

final class GoosializeCookiesPlugin extends Plugin
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
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

        // P1 intentionally contains no consent mutation,
        // tracking interception, banner injection or API writes.
        //
        // P2 introduces the consent domain service.
    }

    public function onTwigSiteVariables(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $assets = $this->grav['assets'];
        $twig = $this->grav['twig'];

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

        $twig->twig_vars[
            'goosialize_cookies_runtime'
        ] = $twig->processTemplate(
            'partials/goosialize-cookies-runtime.html.twig'
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
