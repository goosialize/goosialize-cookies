<?php

declare(strict_types=1);

use Goosialize\Cookies\Presentation\FrontendPresentationRenderer;

require_once
    dirname(__DIR__)
    . '/classes/Presentation/FrontendPresentationRenderer.php';

$assertions = 0;

$check = static function (
    bool $condition,
    string $label
) use (&$assertions): void {
    $assertions++;

    if (!$condition) {
        fwrite(
            STDERR,
            $label . '=FAIL' . PHP_EOL
        );

        exit(1);
    }

    echo $label . '=PASS' . PHP_EOL;
};

$renderer =
    new FrontendPresentationRenderer();

$empty =
    $renderer->render([]);

$check(
    $empty['active'] === false,
    'CORE_ONLY_RENDERER_INACTIVE'
);

$model =
    $renderer->render([
        'banner' => [
            'mode' => 'corner-banner',
            'position' => 'bottom-right',
            'max_width' => 900,
            'edge_spacing' => 30,
            'content_alignment' => 'center',

            'content' => [
                'title' => '<b>Privacy</b>',
                'message' => 'Choose your preferences.',
            ],

            'surface' => [
                'background' => '#AABBCC',
                'background_opacity' => 85,
                'text_color' => '#112233',
                'muted_text_color' => '#445566',
                'accent_color' => '#FFD000',
                'border_color' => '#778899',
                'border_width' => 2,
                'border_style' => 'solid',
                'border_radius' => 18,
                'shadow' => 'large',
                'padding_x' => 30,
                'padding_y' => 22,
                'backdrop_enabled' => false,
            ],

            'buttons' => [
                'layout' => 'row',
                'alignment' => 'center',
                'gap' => 12,
                'height' => 48,

                'accept' => [
                    'label' => 'Allow all',
                    'background' => '#FFD000',
                    'text_color' => '#171717',
                    'border_color' => '#FFD000',
                    'border_width' => 1,
                    'border_radius' => 9,
                ],

                'reject' => [
                    'label' => 'Only necessary',
                ],

                'customize' => [
                    'label' => 'Choose',
                ],
            ],
        ],

        'preferences' => [
            'max_width' => 910,

            'surface' => [
                'border_width' => 0,
                'border_radius' => 10,
                'padding' => 20,
            ],

            'backdrop_enabled' => false,
            'sticky_footer' => true,
        ],

        'settings_button' => [
            'enabled' => false,
            'position' => 'bottom-right',
            'label' => 'Privacy settings',
        ],

        'mobile' => [
            'breakpoint' => 760,
            'banner_mode' => 'bottom-card',
            'full_width' => true,
            'edge_spacing' => 20,
            'stack_buttons' => false,
        ],
    ]);

$check(
    $model['active'] === true,
    'APPEARANCE_RENDERER_ACTIVE'
);

$check(
    $model['banner']['position'] === 'bottom-right',
    'BANNER_POSITION'
);

$check(
    $model['banner']['content']['title'] === 'Privacy',
    'CONTENT_HTML_STRIPPED'
);

$check(
    $model['banner']['surface']['background'] === '#aabbcc',
    'COLOR_NORMALIZED'
);

$check(
    $model['banner']['surface']['backdrop_enabled'] === false,
    'EXPLICIT_FALSE_PRESERVED'
);

$check(
    $model['settings_button']['visible'] === true,
    'WITHDRAWAL_CONTROL_CANNOT_BE_HIDDEN'
);

$check(
    $model['mobile']['stack_buttons'] === false,
    'RESPONSIVE_EXPLICIT_FALSE_PRESERVED'
);

$check(
    str_contains(
        $model['style'],
        '--goo-p-banner-max-width:900px;'
    ),
    'SAFE_CSS_VARIABLE_OUTPUT'
);

$hostile =
    $renderer->render([
        'banner' => [
            'mode' => 'javascript:evil',
            'surface' => [
                'background' =>
                    '#fff;url(https://evil.test/x)',
                'border_style' =>
                    'solid;position:fixed',
            ],
            'content' => [
                'title' =>
                    '<script>alert(1)</script>Safe',
            ],
        ],
    ]);

$check(
    $hostile['banner']['mode'] === 'corner-banner',
    'INVALID_MODE_FALLBACK'
);

$check(
    $hostile['banner']['surface']['background']
        === '#ffffff',
    'CSS_INJECTION_COLOR_REJECTED'
);

$check(
    $hostile['banner']['surface']['border_style']
        === 'solid',
    'CSS_INJECTION_ENUM_REJECTED'
);

$check(
    str_contains(
        strtolower($hostile['style']),
        'url('
    ) === false,
    'NO_URL_CSS_INJECTION'
);

$check(
    str_contains(
        strtolower($hostile['style']),
        'javascript:'
    ) === false,
    'NO_JAVASCRIPT_CSS_INJECTION'
);

$template =
    file_get_contents(
        dirname(__DIR__)
        . '/templates/partials/'
        . 'goosialize-cookies-ui.html.twig'
    );

$check(
    is_string($template),
    'TWIG_READABLE'
);

foreach (
    [
        'data-goosialize-consent-action="accept-all"',
        'data-goosialize-consent-action="reject-all"',
        'data-goosialize-consent-action="manage"',
        'data-goosialize-consent-action="save"',
        'data-goosialize-consent-action="close"',
    ]
    as $action
) {
    $check(
        str_contains(
            $template,
            $action
        ),
        'CONSENT_ACTION_PRESERVED_'
        . strtoupper(
            preg_replace(
                '/[^A-Za-z0-9]+/',
                '_',
                $action
            )
        )
    );
}

$check(
    str_contains(
        $template,
        'data-goosialize-presentation="appearance"'
    ),
    'TWIG_PRESENTATION_CONSUMER'
);

$check(
    str_contains(
        $template,
        'render.style'
    ),
    'TWIG_SAFE_STYLE_CONSUMER'
);

$plugin =
    file_get_contents(
        dirname(__DIR__)
        . '/goosialize-cookies.php'
    );

$check(
    is_string($plugin)
    && str_contains(
        $plugin,
        'FrontendPresentationRenderer'
    ),
    'PLUGIN_RENDERER_WIRING'
);

$check(
    str_contains(
        $plugin,
        'assets/js/presentation.js'
    ),
    'PRESENTATION_JS_REGISTERED'
);

$css =
    file_get_contents(
        dirname(__DIR__)
        . '/assets/css/consent.css'
    );

$check(
    is_string($css),
    'PRESENTATION_CSS_READABLE'
);

$forbiddenDescendantSelectors = [
    '.goo-consent[data-goosialize-presentation="appearance"]'
        . PHP_EOL
        . '    [data-goosialize-effective-content-alignment=',

    '.goo-consent[data-goosialize-presentation="appearance"]'
        . PHP_EOL
        . '    [data-goosialize-button-alignment=',

    '.goo-consent[data-goosialize-presentation="appearance"]'
        . PHP_EOL
        . '    [data-goosialize-button-layout=',

    '.goo-consent[data-goosialize-presentation="appearance"]'
        . PHP_EOL
        . '    [data-goosialize-effective-stack-buttons=',
];

foreach (
    $forbiddenDescendantSelectors
    as $forbiddenSelector
) {
    $check(
        str_contains(
            $css,
            $forbiddenSelector
        ) === false,
        'NO_ROOT_STATE_DESCENDANT_SELECTOR'
    );
}

$requiredRootStates = [
    'data-goosialize-effective-content-alignment="center"',
    'data-goosialize-effective-content-alignment="right"',
    'data-goosialize-button-alignment="center"',
    'data-goosialize-button-alignment="right"',
    'data-goosialize-button-layout="column"',
    'data-goosialize-effective-stack-buttons="true"',
];

foreach ($requiredRootStates as $rootState) {
    $check(
        str_contains(
            $css,
            $rootState
        ),
        'ROOT_STATE_SELECTOR_PRESENT_'
        . strtoupper(
            preg_replace(
                '/[^A-Za-z0-9]+/',
                '_',
                $rootState
            )
        )
    );
}

echo
    'P22_C2_B1_ASSERTIONS='
    . $assertions
    . PHP_EOL;

echo 'ARBITRARY_HTML=FORBIDDEN' . PHP_EOL;
echo 'ARBITRARY_CSS=FORBIDDEN' . PHP_EOL;
echo 'ARBITRARY_JS=FORBIDDEN' . PHP_EOL;
echo 'CONSENT_ACTIONS=CORE_OWNED' . PHP_EOL;
echo 'WITHDRAWAL_CONTROL=CORE_OWNED' . PHP_EOL;
echo 'P22_C2_B1_STATUS=PASS' . PHP_EOL;
