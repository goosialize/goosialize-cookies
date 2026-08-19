<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

final readonly class ConsentSelection
{
    /**
     * @param array<string, bool> $categories
     */
    private function __construct(
        private array $categories,
    ) {
    }

    public static function acceptAll(): self
    {
        return new self([
            ConsentCategory::Necessary->value => true,
            ConsentCategory::Preferences->value => true,
            ConsentCategory::Analytics->value => true,
            ConsentCategory::Marketing->value => true,
        ]);
    }

    public static function rejectOptional(): self
    {
        return new self([
            ConsentCategory::Necessary->value => true,
            ConsentCategory::Preferences->value => false,
            ConsentCategory::Analytics->value => false,
            ConsentCategory::Marketing->value => false,
        ]);
    }

    /**
     * @param array<string, bool> $categories
     */
    public static function custom(array $categories): self
    {
        self::assertKnownCategories($categories);

        return new self([
            ConsentCategory::Necessary->value => true,
            ConsentCategory::Preferences->value => self::boolValue(
                $categories,
                ConsentCategory::Preferences
            ),
            ConsentCategory::Analytics->value => self::boolValue(
                $categories,
                ConsentCategory::Analytics
            ),
            ConsentCategory::Marketing->value => self::boolValue(
                $categories,
                ConsentCategory::Marketing
            ),
        ]);
    }

    public function granted(ConsentCategory $category): bool
    {
        return $this->categories[$category->value];
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return $this->categories;
    }

    public function state(): ConsentState
    {
        if ($this->categories === self::acceptAll()->categories) {
            return ConsentState::AcceptedAll;
        }

        if ($this->categories === self::rejectOptional()->categories) {
            return ConsentState::RejectedOptional;
        }

        return ConsentState::Custom;
    }

    /**
     * @param array<string, bool> $categories
     */
    private static function assertKnownCategories(array $categories): void
    {
        $known = array_map(
            static fn (ConsentCategory $category): string => $category->value,
            ConsentCategory::cases()
        );

        foreach (array_keys($categories) as $category) {
            if (!in_array($category, $known, true)) {
                throw new \InvalidArgumentException(
                    sprintf('Unknown consent category: %s', $category)
                );
            }
        }
    }

    /**
     * @param array<string, bool> $categories
     */
    private static function boolValue(
        array $categories,
        ConsentCategory $category
    ): bool {
        $value = $categories[$category->value] ?? false;

        if (!is_bool($value)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Consent category %s must be boolean.',
                    $category->value
                )
            );
        }

        return $value;
    }
}
