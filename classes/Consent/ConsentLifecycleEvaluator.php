<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

final class ConsentLifecycleEvaluator
{
    public function __construct(
        private readonly ConsentVersion $currentVersion,
        private readonly int $lifetimeDays,
    ) {
        if ($lifetimeDays < 1) {
            throw new \InvalidArgumentException(
                'Consent lifetime must be >= 1 day.'
            );
        }
    }

    public function evaluate(
        ?string $serialized,
        ?\DateTimeImmutable $now = null
    ): ConsentLifecycleStatus {
        if (
            $serialized === null
            || $serialized === ''
        ) {
            return ConsentLifecycleStatus::Missing;
        }

        try {
            $decoded = json_decode(
                $serialized,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            return ConsentLifecycleStatus::Malformed;
        }

        if (!is_array($decoded)) {
            return ConsentLifecycleStatus::Malformed;
        }

        $version =
            $decoded['version'] ?? null;

        if (
            !is_int($version)
            || $version < 1
        ) {
            return ConsentLifecycleStatus::Malformed;
        }

        if (
            $version
            !== $this->currentVersion->value
        ) {
            return ConsentLifecycleStatus::VersionMismatch;
        }

        $timestamp =
            $decoded['timestamp'] ?? null;

        if (
            !is_string($timestamp)
            || $timestamp === ''
        ) {
            return ConsentLifecycleStatus::Malformed;
        }

        try {
            $recordedAt =
                new \DateTimeImmutable(
                    $timestamp
                );
        } catch (\Throwable) {
            return ConsentLifecycleStatus::Malformed;
        }

        $now ??=
            new \DateTimeImmutable();

        if ($recordedAt > $now) {
            return ConsentLifecycleStatus::FutureTimestamp;
        }

        $expiresAt =
            $recordedAt->modify(
                sprintf(
                    '+%d days',
                    $this->lifetimeDays
                )
            );

        if ($now > $expiresAt) {
            return ConsentLifecycleStatus::Expired;
        }

        if (
            !(new ConsentValidator())
                ->isValidPayload(
                    $decoded,
                    $this->currentVersion
                )
        ) {
            return ConsentLifecycleStatus::Malformed;
        }

        return ConsentLifecycleStatus::Valid;
    }
}
