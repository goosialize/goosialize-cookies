<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Presentation;

use Throwable;

final class PresentationBridge
{
    /**
     * @param array<string, mixed> $context
     */
    public function resolve(
        mixed $provider,
        array $context = []
    ): ResolvedPresentation {
        if (
            !$provider instanceof PresentationProviderInterface
        ) {
            return new ResolvedPresentation();
        }

        try {
            return $provider->resolve($context);
        } catch (Throwable) {
            return new ResolvedPresentation();
        }
    }
}
