<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Appearance;

enum AppearancePreset: string
{
    case Goosialize = 'goosialize';
    case Minimal = 'minimal';
    case Classic = 'classic';
    case Soft = 'soft';
    case HighContrast = 'high-contrast';
    case Custom = 'custom';
}
