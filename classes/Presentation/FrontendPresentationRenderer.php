<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Presentation;

final class FrontendPresentationRenderer
{
    /**
     * Convert provider metadata into a bounded frontend-only model.
     *
     * Provider metadata is never trusted as raw CSS, HTML, Twig or JS.
     *
     * @param array<string, mixed> $presentation
     * @return array<string, mixed>
     */
    public function render(array $presentation): array
    {
        if ($presentation === []) {
            return [
                'active' => false,
                'style' => '',
            ];
        }

        $banner = $this->map($presentation['banner'] ?? null);
        $content = $this->map($banner['content'] ?? null);
        $surface = $this->map($banner['surface'] ?? null);
        $typography = $this->map($banner['typography'] ?? null);
        $buttons = $this->map($banner['buttons'] ?? null);

        $preferences =
            $this->map($presentation['preferences'] ?? null);

        $preferencesSurface =
            $this->map($preferences['surface'] ?? null);

        $categories =
            $this->map($preferences['categories'] ?? null);

        $toggles =
            $this->map($preferences['toggles'] ?? null);

        $preferencesButtons =
            $this->map($preferences['buttons'] ?? null);

        $settings =
            $this->map($presentation['settings_button'] ?? null);

        $tablet =
            $this->responsive(
                $this->map($presentation['tablet'] ?? null),
                false
            );

        $mobile =
            $this->responsive(
                $this->map($presentation['mobile'] ?? null),
                true
            );

        $model = [
            'active' => true,

            'banner' => [
                'mode' => $this->enum(
                    $banner['mode'] ?? null,
                    [
                        'popup',
                        'full-width-bottom',
                        'corner-banner',
                        'full-width-top',
                        'compact-floating',
                    ],
                    'corner-banner'
                ),

                'position' => $this->enum(
                    $banner['position'] ?? null,
                    [
                        'bottom-left',
                        'bottom-right',
                    ],
                    'bottom-left'
                ),

                'max_width' => $this->integer(
                    $banner['max_width'] ?? null,
                    860,
                    320,
                    1600
                ),

                'edge_spacing' => $this->integer(
                    $banner['edge_spacing'] ?? null,
                    24,
                    0,
                    96
                ),

                'content_alignment' => $this->enum(
                    $banner['content_alignment'] ?? null,
                    ['left', 'center', 'right'],
                    'left'
                ),

                'content' => [
                    'title' => $this->text(
                        $content['title'] ?? null,
                        null,
                        160
                    ),

                    'message' => $this->text(
                        $content['message'] ?? null,
                        null,
                        1200
                    ),
                ],

                'surface' => [
                    'background' => $this->color(
                        $surface['background'] ?? null,
                        '#ffffff'
                    ),

                    'background_opacity' => $this->integer(
                        $surface['background_opacity'] ?? null,
                        100,
                        0,
                        100
                    ),

                    'text_color' => $this->color(
                        $surface['text_color'] ?? null,
                        '#171717'
                    ),

                    'muted_text_color' => $this->color(
                        $surface['muted_text_color'] ?? null,
                        '#666666'
                    ),

                    'accent_color' => $this->color(
                        $surface['accent_color'] ?? null,
                        '#ffd000'
                    ),

                    'border_color' => $this->color(
                        $surface['border_color'] ?? null,
                        '#d8d8d8'
                    ),

                    'border_width' => $this->integer(
                        $surface['border_width'] ?? null,
                        1,
                        0,
                        8
                    ),

                    'border_style' => $this->enum(
                        $surface['border_style'] ?? null,
                        [
                            'solid',
                            'dashed',
                            'dotted',
                            'double',
                        ],
                        'solid'
                    ),

                    'border_radius' => $this->integer(
                        $surface['border_radius'] ?? null,
                        14,
                        0,
                        64
                    ),

                    'shadow' => $this->enum(
                        $surface['shadow'] ?? null,
                        [
                            'none',
                            'small',
                            'medium',
                            'large',
                        ],
                        'medium'
                    ),

                    'padding_x' => $this->integer(
                        $surface['padding_x'] ?? null,
                        24,
                        0,
                        96
                    ),

                    'padding_y' => $this->integer(
                        $surface['padding_y'] ?? null,
                        24,
                        0,
                        96
                    ),

                    'backdrop_enabled' => $this->boolean(
                        $surface['backdrop_enabled'] ?? null,
                        false
                    ),
                ],

                'typography' => [
                    'title_size' => $this->enum(
                        $typography['title_size'] ?? null,
                        ['small', 'medium', 'large'],
                        'medium'
                    ),

                    'title_weight' => $this->integer(
                        $typography['title_weight'] ?? null,
                        700,
                        100,
                        900
                    ),

                    'message_size' => $this->enum(
                        $typography['message_size'] ?? null,
                        ['small', 'medium', 'large'],
                        'small'
                    ),

                    'message_weight' => $this->integer(
                        $typography['message_weight'] ?? null,
                        400,
                        100,
                        900
                    ),

                    'line_height' => $this->integer(
                        $typography['line_height'] ?? null,
                        150,
                        100,
                        240
                    ),

                    'text_alignment' => $this->enum(
                        $typography['text_alignment'] ?? null,
                        ['left', 'center', 'right'],
                        'left'
                    ),
                ],

                'buttons' => [
                    'layout' => $this->enum(
                        $buttons['layout'] ?? null,
                        ['row', 'column', 'wrap'],
                        'row'
                    ),

                    'alignment' => $this->enum(
                        $buttons['alignment'] ?? null,
                        ['left', 'center', 'right'],
                        'left'
                    ),

                    'gap' => $this->integer(
                        $buttons['gap'] ?? null,
                        10,
                        0,
                        48
                    ),

                    'height' => $this->integer(
                        $buttons['height'] ?? null,
                        46,
                        32,
                        80
                    ),

                    'accept' => $this->button(
                        $this->map($buttons['accept'] ?? null),
                        'Accept All',
                        '#ffd000',
                        '#171717',
                        '#ffd000'
                    ),

                    'reject' => $this->button(
                        $this->map($buttons['reject'] ?? null),
                        'Reject All',
                        '#ffffff',
                        '#171717',
                        '#d8d8d8'
                    ),

                    'customize' => $this->button(
                        $this->map($buttons['customize'] ?? null),
                        'Customize',
                        '#ffffff',
                        '#171717',
                        '#d8d8d8'
                    ),
                ],
            ],

            'preferences' => [
                'max_width' => $this->integer(
                    $preferences['max_width'] ?? null,
                    860,
                    320,
                    1600
                ),

                'max_height' => $this->dimension(
                    $preferences['max_height'] ?? null,
                    '86dvh'
                ),

                'surface' => [
                    'background' => $this->color(
                        $preferencesSurface['background'] ?? null,
                        '#ffffff'
                    ),

                    'text_color' => $this->color(
                        $preferencesSurface['text_color'] ?? null,
                        '#171717'
                    ),

                    'muted_text_color' => $this->color(
                        $preferencesSurface['muted_text_color'] ?? null,
                        '#666666'
                    ),

                    'accent_color' => $this->color(
                        $preferencesSurface['accent_color'] ?? null,
                        '#ffd000'
                    ),

                    'border_color' => $this->color(
                        $preferencesSurface['border_color'] ?? null,
                        '#d8d8d8'
                    ),

                    'border_width' => $this->integer(
                        $preferencesSurface['border_width'] ?? null,
                        1,
                        0,
                        8
                    ),

                    'border_radius' => $this->integer(
                        $preferencesSurface['border_radius'] ?? null,
                        14,
                        0,
                        64
                    ),

                    'shadow' => $this->enum(
                        $preferencesSurface['shadow'] ?? null,
                        [
                            'none',
                            'small',
                            'medium',
                            'large',
                        ],
                        'medium'
                    ),

                    'padding' => $this->integer(
                        $preferencesSurface['padding'] ?? null,
                        24,
                        8,
                        64
                    ),
                ],

                'categories' => [
                    'gap' => $this->integer(
                        $categories['gap'] ?? null,
                        12,
                        0,
                        48
                    ),

                    'separator_color' => $this->color(
                        $categories['separator_color'] ?? null,
                        '#e6e6e6'
                    ),

                    'border_radius' => $this->integer(
                        $categories['border_radius'] ?? null,
                        8,
                        0,
                        64
                    ),
                ],

                'toggles' => [
                    'style' => $this->enum(
                        $toggles['style'] ?? null,
                        ['switch', 'square'],
                        'switch'
                    ),

                    'active_color' => $this->color(
                        $toggles['active_color'] ?? null,
                        '#ffd000'
                    ),

                    'inactive_color' => $this->color(
                        $toggles['inactive_color'] ?? null,
                        '#b8b8b8'
                    ),
                ],

                'buttons' => [
                    'height' => $this->integer(
                        $preferencesButtons['height'] ?? null,
                        46,
                        32,
                        80
                    ),

                    'gap' => $this->integer(
                        $preferencesButtons['gap'] ?? null,
                        10,
                        0,
                        48
                    ),

                    'border_radius' => $this->integer(
                        $preferencesButtons['border_radius'] ?? null,
                        8,
                        0,
                        64
                    ),
                ],

                'backdrop_enabled' => $this->boolean(
                    $preferences['backdrop_enabled'] ?? null,
                    true
                ),

                'backdrop_color' => $this->color(
                    $preferences['backdrop_color'] ?? null,
                    '#000000'
                ),

                'backdrop_opacity' => $this->integer(
                    $preferences['backdrop_opacity'] ?? null,
                    45,
                    0,
                    100
                ),

                'sticky_footer' => $this->boolean(
                    $preferences['sticky_footer'] ?? null,
                    true
                ),
            ],

            /*
             * Withdrawal/settings access is owned by the Core.
             * An addon may style or label it but cannot suppress it.
             */
            'settings_button' => [
                'visible' => true,

                'form' => $this->enum(
                    $settings['form'] ?? null,
                    ['icon', 'text', 'icon-text'],
                    'icon-text'
                ),

                'position' => $this->enum(
                    $settings['position'] ?? null,
                    ['bottom-left', 'bottom-right'],
                    'bottom-left'
                ),

                'label' => $this->text(
                    $settings['label'] ?? null,
                    null,
                    120
                ),

                'edge_spacing' => $this->integer(
                    $settings['edge_spacing'] ?? null,
                    20,
                    0,
                    96
                ),

                'size' => $this->integer(
                    $settings['size'] ?? null,
                    48,
                    36,
                    80
                ),

                'background' => $this->color(
                    $settings['background'] ?? null,
                    '#171717'
                ),

                'text_color' => $this->color(
                    $settings['text_color'] ?? null,
                    '#ffffff'
                ),

                'border_color' => $this->color(
                    $settings['border_color'] ?? null,
                    '#171717'
                ),

                'border_width' => $this->integer(
                    $settings['border_width'] ?? null,
                    1,
                    0,
                    8
                ),

                'border_radius' => $this->integer(
                    $settings['border_radius'] ?? null,
                    24,
                    0,
                    64
                ),

                'shadow' => $this->enum(
                    $settings['shadow'] ?? null,
                    ['none', 'small', 'medium', 'large'],
                    'small'
                ),
            ],

            'tablet' => $tablet,
            'mobile' => $mobile,
        ];

        $model['style'] = $this->style($model);

        return $model;
    }

    /**
     * @return array<string, mixed>
     */
    private function responsive(
        array $input,
        bool $mobile
    ): array {
        return [
            'breakpoint' => $this->integer(
                $input['breakpoint'] ?? null,
                $mobile ? 760 : 1024,
                $mobile ? 320 : 600,
                $mobile ? 900 : 1440
            ),

            'banner_mode' => $this->enum(
                $input['banner_mode'] ?? null,
                [
                    'inherit',
                    'bottom-card',
                    'bottom-sheet',
                    'full-width-bottom',
                    'popup',
                ],
                $mobile ? 'bottom-card' : 'inherit'
            ),

            'full_width' => $this->boolean(
                $input['full_width'] ?? null,
                $mobile
            ),

            'edge_spacing' => $this->integer(
                $input['edge_spacing'] ?? null,
                $mobile ? 12 : 20,
                0,
                96
            ),

            'stack_buttons' => $this->boolean(
                $input['stack_buttons'] ?? null,
                $mobile
            ),

            'content_alignment' => $this->enum(
                $input['content_alignment'] ?? null,
                ['left', 'center', 'right'],
                'left'
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function button(
        array $button,
        string $defaultLabel,
        string $defaultBackground,
        string $defaultText,
        string $defaultBorder
    ): array {
        return [
            'label' => $this->text(
                $button['label'] ?? null,
                $defaultLabel,
                120
            ),

            'background' => $this->color(
                $button['background'] ?? null,
                $defaultBackground
            ),

            'text_color' => $this->color(
                $button['text_color'] ?? null,
                $defaultText
            ),

            'border_color' => $this->color(
                $button['border_color'] ?? null,
                $defaultBorder
            ),

            'border_width' => $this->integer(
                $button['border_width'] ?? null,
                1,
                0,
                8
            ),

            'border_radius' => $this->integer(
                $button['border_radius'] ?? null,
                8,
                0,
                64
            ),
        ];
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private function map(mixed $value): array
    {
        return is_array($value)
            ? $value
            : [];
    }

    /**
     * @param list<string> $allowed
     */
    private function enum(
        mixed $value,
        array $allowed,
        string $fallback
    ): string {
        return is_string($value)
            && in_array($value, $allowed, true)
                ? $value
                : $fallback;
    }

    private function integer(
        mixed $value,
        int $fallback,
        int $minimum,
        int $maximum
    ): int {
        if (!is_int($value)) {
            return $fallback;
        }

        return max(
            $minimum,
            min($maximum, $value)
        );
    }

    private function boolean(
        mixed $value,
        bool $fallback
    ): bool {
        return is_bool($value)
            ? $value
            : $fallback;
    }

    private function color(
        mixed $value,
        string $fallback
    ): string {
        if (
            !is_string($value)
            || preg_match(
                '/^#[0-9a-fA-F]{6}$/D',
                $value
            ) !== 1
        ) {
            return strtolower($fallback);
        }

        return strtolower($value);
    }

    private function text(
        mixed $value,
        ?string $fallback,
        int $maximumLength
    ): ?string {
        if (!is_string($value)) {
            return $fallback;
        }

        $value = trim(
            strip_tags($value)
        );

        if (
            $value === ''
            || preg_match(
                '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
                $value
            ) === 1
        ) {
            return $fallback;
        }

        return mb_substr(
            $value,
            0,
            $maximumLength
        );
    }

    private function dimension(
        mixed $value,
        string $fallback
    ): string {
        if (!is_string($value)) {
            return $fallback;
        }

        if (
            preg_match(
                '/^(?:[4-9][0-9]|100)(?:vh|dvh)$/D',
                $value
            ) === 1
        ) {
            return $value;
        }

        if (
            preg_match(
                '/^(?:[2-9][0-9]{2}|1[01][0-9]{2}|1200)px$/D',
                $value
            ) === 1
        ) {
            return $value;
        }

        return $fallback;
    }

    /**
     * @param array<string, mixed> $model
     */
    private function style(array $model): string
    {
        $banner = $model['banner'];
        $surface = $banner['surface'];
        $typography = $banner['typography'];
        $buttons = $banner['buttons'];
        $preferences = $model['preferences'];
        $preferencesSurface = $preferences['surface'];
        $settings = $model['settings_button'];

        $variables = [
            '--goo-p-banner-max-width' =>
                $banner['max_width'] . 'px',

            '--goo-p-banner-edge' =>
                $banner['edge_spacing'] . 'px',

            '--goo-p-banner-background' =>
                $this->rgba(
                    $surface['background'],
                    $surface['background_opacity']
                ),

            '--goo-p-banner-text' =>
                $surface['text_color'],

            '--goo-p-banner-muted' =>
                $surface['muted_text_color'],

            '--goo-p-banner-accent' =>
                $surface['accent_color'],

            '--goo-p-banner-border' =>
                $surface['border_color'],

            '--goo-p-banner-border-width' =>
                $surface['border_width'] . 'px',

            '--goo-p-banner-border-style' =>
                $surface['border_style'],

            '--goo-p-banner-radius' =>
                $surface['border_radius'] . 'px',

            '--goo-p-banner-shadow' =>
                $this->shadow($surface['shadow']),

            '--goo-p-banner-padding-x' =>
                $surface['padding_x'] . 'px',

            '--goo-p-banner-padding-y' =>
                $surface['padding_y'] . 'px',

            '--goo-p-banner-title-size' =>
                $this->fontSize(
                    $typography['title_size'],
                    true
                ),

            '--goo-p-banner-title-weight' =>
                (string) $typography['title_weight'],

            '--goo-p-banner-message-size' =>
                $this->fontSize(
                    $typography['message_size'],
                    false
                ),

            '--goo-p-banner-message-weight' =>
                (string) $typography['message_weight'],

            '--goo-p-banner-line-height' =>
                ($typography['line_height'] / 100),

            '--goo-p-button-gap' =>
                $buttons['gap'] . 'px',

            '--goo-p-button-height' =>
                $buttons['height'] . 'px',

            '--goo-p-accept-background' =>
                $buttons['accept']['background'],

            '--goo-p-accept-text' =>
                $buttons['accept']['text_color'],

            '--goo-p-accept-border' =>
                $buttons['accept']['border_color'],

            '--goo-p-accept-border-width' =>
                $buttons['accept']['border_width'] . 'px',

            '--goo-p-accept-radius' =>
                $buttons['accept']['border_radius'] . 'px',

            '--goo-p-reject-background' =>
                $buttons['reject']['background'],

            '--goo-p-reject-text' =>
                $buttons['reject']['text_color'],

            '--goo-p-reject-border' =>
                $buttons['reject']['border_color'],

            '--goo-p-reject-border-width' =>
                $buttons['reject']['border_width'] . 'px',

            '--goo-p-reject-radius' =>
                $buttons['reject']['border_radius'] . 'px',

            '--goo-p-customize-background' =>
                $buttons['customize']['background'],

            '--goo-p-customize-text' =>
                $buttons['customize']['text_color'],

            '--goo-p-customize-border' =>
                $buttons['customize']['border_color'],

            '--goo-p-customize-border-width' =>
                $buttons['customize']['border_width'] . 'px',

            '--goo-p-customize-radius' =>
                $buttons['customize']['border_radius'] . 'px',

            '--goo-p-preferences-max-width' =>
                $preferences['max_width'] . 'px',

            '--goo-p-preferences-max-height' =>
                $preferences['max_height'],

            '--goo-p-preferences-background' =>
                $preferencesSurface['background'],

            '--goo-p-preferences-text' =>
                $preferencesSurface['text_color'],

            '--goo-p-preferences-muted' =>
                $preferencesSurface['muted_text_color'],

            '--goo-p-preferences-accent' =>
                $preferencesSurface['accent_color'],

            '--goo-p-preferences-border' =>
                $preferencesSurface['border_color'],

            '--goo-p-preferences-border-width' =>
                $preferencesSurface['border_width'] . 'px',

            '--goo-p-preferences-radius' =>
                $preferencesSurface['border_radius'] . 'px',

            '--goo-p-preferences-shadow' =>
                $this->shadow(
                    $preferencesSurface['shadow']
                ),

            '--goo-p-preferences-padding' =>
                $preferencesSurface['padding'] . 'px',

            '--goo-p-category-gap' =>
                $preferences['categories']['gap'] . 'px',

            '--goo-p-category-separator' =>
                $preferences['categories']['separator_color'],

            '--goo-p-toggle-active' =>
                $preferences['toggles']['active_color'],

            '--goo-p-toggle-inactive' =>
                $preferences['toggles']['inactive_color'],

            '--goo-p-preferences-button-height' =>
                $preferences['buttons']['height'] . 'px',

            '--goo-p-preferences-button-gap' =>
                $preferences['buttons']['gap'] . 'px',

            '--goo-p-preferences-button-radius' =>
                $preferences['buttons']['border_radius'] . 'px',

            '--goo-p-preferences-backdrop' =>
                $this->rgba(
                    $preferences['backdrop_color'],
                    $preferences['backdrop_opacity']
                ),

            '--goo-p-settings-edge' =>
                $settings['edge_spacing'] . 'px',

            '--goo-p-settings-size' =>
                $settings['size'] . 'px',

            '--goo-p-settings-background' =>
                $settings['background'],

            '--goo-p-settings-text' =>
                $settings['text_color'],

            '--goo-p-settings-border' =>
                $settings['border_color'],

            '--goo-p-settings-border-width' =>
                $settings['border_width'] . 'px',

            '--goo-p-settings-radius' =>
                $settings['border_radius'] . 'px',

            '--goo-p-settings-shadow' =>
                $this->shadow(
                    $settings['shadow']
                ),
        ];

        $style = '';

        foreach ($variables as $name => $value) {
            $style .= $name
                . ':'
                . $value
                . ';';
        }

        return $style;
    }

    private function rgba(
        string $hex,
        int $opacity
    ): string {
        $red = hexdec(substr($hex, 1, 2));
        $green = hexdec(substr($hex, 3, 2));
        $blue = hexdec(substr($hex, 5, 2));

        $alpha = rtrim(
            rtrim(
                number_format(
                    $opacity / 100,
                    2,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );

        return sprintf(
            'rgba(%d,%d,%d,%s)',
            $red,
            $green,
            $blue,
            $alpha === ''
                ? '0'
                : $alpha
        );
    }

    private function shadow(
        string $token
    ): string {
        return match ($token) {
            'none' => 'none',
            'small' =>
                '0 8px 28px rgba(0,0,0,0.14)',
            'large' =>
                '0 24px 70px rgba(0,0,0,0.22)',
            default =>
                '0 18px 50px rgba(0,0,0,0.16)',
        };
    }

    private function fontSize(
        string $token,
        bool $title
    ): string {
        if ($title) {
            return match ($token) {
                'small' => '1.1rem',
                'large' => '1.75rem',
                default => '1.4rem',
            };
        }

        return match ($token) {
            'medium' => '1rem',
            'large' => '1.1rem',
            default => '0.9rem',
        };
    }
}
