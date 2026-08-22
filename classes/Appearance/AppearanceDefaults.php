<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Appearance;

final class AppearanceDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function values(): array
    {
        return [
            'preset' => AppearancePreset::Goosialize->value,

            'banner' => [
                'mode' => 'corner-banner',
                'position' => 'bottom-left',
                'max_width' => 860,
                'edge_spacing' => 24,
                'content_alignment' => 'left',

                'surface' => [
                    'background' => '#ffffff',
                    'text_color' => '#171717',
                    'muted_text_color' => '#666666',
                    'accent_color' => '#ffd000',
                    'border_color' => '#d8d8d8',
                    'border_width' => 1,
                    'border_radius' => 14,
                    'shadow' => 'medium',
                    'padding' => 24,
                    'backdrop_enabled' => false,
                ],

                'typography' => [
                    'title_size' => 'medium',
                    'message_size' => 'small',
                    'text_alignment' => 'left',
                ],

                'buttons' => [
                    'layout' => 'row',
                    'height' => 46,
                ],

                'icon' => [
                    'enabled' => false,
                    'name' => 'none',
                ],

                'animation' => [
                    'entry' => 'fade',
                    'exit' => 'fade',
                    'duration_ms' => 220,
                ],
            ],

            'preferences' => [
                'max_width' => 860,
                'max_height' => '86dvh',
                'background' => '#ffffff',
                'text_color' => '#171717',
                'accent_color' => '#ffd000',
                'border_radius' => 14,
                'shadow' => 'medium',
                'backdrop_enabled' => true,
                'sticky_footer' => true,
                'animation_duration_ms' => 220,
            ],

            'settings_button' => [
                'enabled' => true,
                'form' => 'icon-text',
                'position' => 'bottom-left',
                'icon_name' => 'settings',
                'edge_spacing' => 20,
            ],

            'mobile' => [
                'breakpoint' => 760,
                'banner_mode' => 'bottom-card',
                'full_width' => true,
                'edge_spacing' => 12,
                'stack_buttons' => true,
                'content_alignment' => 'left',
            ],
        ];
    }
}
