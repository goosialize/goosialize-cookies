<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

final readonly class ConsentVersion
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 1) {
            throw new \InvalidArgumentException(
                'Consent version must be >= 1.'
            );
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
