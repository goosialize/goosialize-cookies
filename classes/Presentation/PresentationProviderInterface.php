<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Presentation;

interface PresentationProviderInterface
{
    public function resolve(
        array $context = []
    ): ResolvedPresentation;
}
