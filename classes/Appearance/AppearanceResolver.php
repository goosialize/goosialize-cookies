<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Appearance;

final class AppearanceResolver
{
    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function resolve(array $config = []): array
    {
        $presetValue = $config['preset'] ?? AppearancePreset::Goosialize->value;

        $preset = is_string($presetValue)
            ? AppearancePreset::tryFrom($presetValue)
            : null;

        $preset ??= AppearancePreset::Goosialize;

        $resolved = $this->mergeKnown(
            AppearanceDefaults::values(),
            AppearancePresetValues::forPreset($preset)
        );

        $resolved = $this->mergeKnown(
            $resolved,
            $config
        );

        $resolved['preset'] = $preset->value;

        return $this->normalize($resolved);
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function mergeKnown(
        array $base,
        array $overrides
    ): array {
        foreach ($overrides as $key => $value) {
            if (!array_key_exists($key, $base)) {
                continue;
            }

            if (
                is_array($base[$key])
                && is_array($value)
            ) {
                $base[$key] = $this->mergeKnown(
                    $base[$key],
                    $value
                );
                continue;
            }

            if (is_array($base[$key])) {
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function normalize(array $config): array
    {
        $config['banner']['max_width'] = $this->boundedInt(
            $config['banner']['max_width'] ?? 860,
            280,
            1600,
            860
        );

        $config['banner']['edge_spacing'] = $this->boundedInt(
            $config['banner']['edge_spacing'] ?? 24,
            0,
            96,
            24
        );

        $config['banner']['surface']['border_width'] = $this->boundedInt(
            $config['banner']['surface']['border_width'] ?? 1,
            0,
            8,
            1
        );

        $config['banner']['surface']['border_radius'] = $this->boundedInt(
            $config['banner']['surface']['border_radius'] ?? 14,
            0,
            64,
            14
        );

        $config['banner']['surface']['padding'] = $this->boundedInt(
            $config['banner']['surface']['padding'] ?? 24,
            8,
            64,
            24
        );

        $config['banner']['animation']['duration_ms'] = $this->boundedInt(
            $config['banner']['animation']['duration_ms'] ?? 220,
            0,
            800,
            220
        );

        $config['preferences']['animation_duration_ms'] = $this->boundedInt(
            $config['preferences']['animation_duration_ms'] ?? 220,
            0,
            800,
            220
        );

        $config['mobile']['breakpoint'] = $this->boundedInt(
            $config['mobile']['breakpoint'] ?? 760,
            320,
            1280,
            760
        );

        return $config;
    }

    private function boundedInt(
        mixed $value,
        int $min,
        int $max,
        int $default
    ): int {
        if (!is_int($value)) {
            return $default;
        }

        return max(
            $min,
            min($max, $value)
        );
    }
}
