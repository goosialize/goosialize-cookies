<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Presentation;

final readonly class ResolvedPresentation
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public array $metadata = []
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->metadata;
    }
}
