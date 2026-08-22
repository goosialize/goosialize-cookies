<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Appearance;

final class AppearancePresetValues
{
    /**
     * @return array<string, mixed>
     */
    public static function forPreset(
        AppearancePreset $preset
    ): array {
        return match ($preset) {
            AppearancePreset::Goosialize => [
                'banner' => [
                    'surface' => [
                        'accent_color' => '#ffd000',
                        'border_radius' => 14,
                        'shadow' => 'medium',
                    ],
                ],
            ],

            AppearancePreset::Minimal => [
                'banner' => [
                    'surface' => [
                        'border_width' => 0,
                        'border_radius' => 8,
                        'shadow' => 'none',
                    ],
                    'animation' => [
                        'entry' => 'none',
                        'exit' => 'none',
                    ],
                ],
            ],

            AppearancePreset::Classic => [
                'banner' => [
                    'surface' => [
                        'border_radius' => 4,
                        'shadow' => 'small',
                    ],
                    'typography' => [
                        'text_alignment' => 'left',
                    ],
                ],
            ],

            AppearancePreset::Soft => [
                'banner' => [
                    'surface' => [
                        'background' => '#f8f8f8',
                        'border_radius' => 20,
                        'shadow' => 'small',
                    ],
                ],
                'preferences' => [
                    'background' => '#f8f8f8',
                    'border_radius' => 20,
                ],
            ],

            AppearancePreset::HighContrast => [
                'banner' => [
                    'surface' => [
                        'background' => '#000000',
                        'text_color' => '#ffffff',
                        'muted_text_color' => '#ffffff',
                        'accent_color' => '#ffd000',
                        'border_color' => '#ffffff',
                        'border_width' => 2,
                        'shadow' => 'none',
                    ],
                ],
                'preferences' => [
                    'background' => '#000000',
                    'text_color' => '#ffffff',
                    'accent_color' => '#ffd000',
                ],
            ],

            AppearancePreset::Custom => [],
        };
    }
}
