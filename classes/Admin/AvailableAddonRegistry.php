<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Admin;

final class AvailableAddonRegistry
{
    /**
     * @return list<array<string, string>>
     */
    public static function all(): array
    {
        return [
            [
                'slug' =>
                    'goosialize-cookies-appearance',
                'name' =>
                    'Goosialize Cookies Appearance',
                'kind' =>
                    'Paid commercial add-on',
                'minimum_free_version' =>
                    '1.1.1',
                'description' =>
                    'Advanced presentation controls for the '
                    . 'Goosialize Cookies consent interface.',
            ],
        ];
    }
}
