<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

enum ConsentCategory: string
{
    case Necessary = 'necessary';
    case Preferences = 'preferences';
    case Analytics = 'analytics';
    case Marketing = 'marketing';

    public function isRequired(): bool
    {
        return $this === self::Necessary;
    }

    public function defaultGranted(): bool
    {
        return $this === self::Necessary;
    }
}
