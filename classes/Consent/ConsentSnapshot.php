<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

final readonly class ConsentSnapshot
{
    public function __construct(
        public ConsentVersion $version,
        public ConsentSelection $selection,
        public \DateTimeImmutable $recordedAt,
    ) {
    }

    public function state(): ConsentState
    {
        return $this->selection->state();
    }

    public function granted(ConsentCategory $category): bool
    {
        return $this->selection->granted($category);
    }
}
