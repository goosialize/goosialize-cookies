<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

final class ConsentValidator
{
    /**
     * @param mixed $payload
     */
    public function isValidPayload(
        mixed $payload,
        ConsentVersion $currentVersion
    ): bool {
        if (!is_array($payload)) {
            return false;
        }

        if (($payload['version'] ?? null) !== $currentVersion->value) {
            return false;
        }

        $timestamp = $payload['timestamp'] ?? null;
        if (!is_string($timestamp) || $timestamp === '') {
            return false;
        }

        try {
            new \DateTimeImmutable($timestamp);
        } catch (\Throwable) {
            return false;
        }

        $categories = $payload['categories'] ?? null;
        if (!is_array($categories)) {
            return false;
        }

        $known = array_map(
            static fn (ConsentCategory $category): string => $category->value,
            ConsentCategory::cases()
        );

        foreach (array_keys($categories) as $category) {
            if (!is_string($category)) {
                return false;
            }

            if (!in_array($category, $known, true)) {
                return false;
            }
        }

        foreach ($known as $category) {
            if (!array_key_exists($category, $categories)) {
                return false;
            }

            if (!is_bool($categories[$category])) {
                return false;
            }
        }

        if ($categories[ConsentCategory::Necessary->value] !== true) {
            return false;
        }

        return true;
    }
}
