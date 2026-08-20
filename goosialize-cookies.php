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
            'onOutputGenerated' => ['onOutputGenerated', 0],
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

    private function isEnabled(): bool
    {
        return (bool) $this->config->get(
            'plugins.goosialize-cookies.enabled',
            true
        );
    }
}
