<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Integration\Google;

use Goosialize\Cookies\Consent\ConsentCategory;

final class ConsentMode
{
    /**
     * @param array<string, bool> $consent
     * @return array{
     *   analytics_storage: string,
     *   ad_storage: string,
     *   ad_user_data: string,
     *   ad_personalization: string
     * }
     */
    public function map(array $consent): array
    {
        $analytics = $consent[ConsentCategory::Analytics->value] ?? false;
        $marketing = $consent[ConsentCategory::Marketing->value] ?? false;

        return [
            'analytics_storage' => $analytics ? 'granted' : 'denied',
            'ad_storage' => $marketing ? 'granted' : 'denied',
            'ad_user_data' => $marketing ? 'granted' : 'denied',
            'ad_personalization' => $marketing ? 'granted' : 'denied',
        ];
    }
}
