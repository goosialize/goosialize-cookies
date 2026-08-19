<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

final class ConsentEngine
{
    public function __construct(
        private readonly ConsentVersion $currentVersion,
    ) {
    }

    public function decide(
        ConsentDecision $decision,
        ?array $custom = null,
        ?\DateTimeImmutable $recordedAt = null
    ): ConsentSnapshot {
        $selection = match ($decision) {
            ConsentDecision::AcceptAll =>
                ConsentSelection::acceptAll(),

            ConsentDecision::RejectOptional =>
                ConsentSelection::rejectOptional(),

            ConsentDecision::Custom =>
                ConsentSelection::custom($custom ?? []),
        };

        return new ConsentSnapshot(
            $this->currentVersion,
            $selection,
            $recordedAt ?? new \DateTimeImmutable()
        );
    }

    public function restore(string $serialized): ?ConsentSnapshot
    {
        return (new ConsentSerializer())->deserialize(
            $serialized,
            $this->currentVersion
        );
    }

    public function isCurrent(ConsentSnapshot $snapshot): bool
    {
        return $snapshot->version->equals($this->currentVersion);
    }
}
