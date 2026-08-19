<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

final class ConsentSerializer
{
    public function serialize(ConsentSnapshot $snapshot): string
    {
        $payload = [
            'version' => $snapshot->version->value,
            'timestamp' => $snapshot->recordedAt->format(DATE_ATOM),
            'categories' => $snapshot->selection->toArray(),
        ];

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        return $json;
    }

    public function deserialize(
        string $payload,
        ConsentVersion $currentVersion
    ): ?ConsentSnapshot {
        try {
            $decoded = json_decode(
                $payload,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            return null;
        }

        $validator = new ConsentValidator();

        if (!$validator->isValidPayload($decoded, $currentVersion)) {
            return null;
        }

        /** @var array{
         *   version:int,
         *   timestamp:string,
         *   categories:array<string,bool>
         * } $decoded
         */

        return new ConsentSnapshot(
            new ConsentVersion($decoded['version']),
            ConsentSelection::custom($decoded['categories']),
            new \DateTimeImmutable($decoded['timestamp'])
        );
    }
}
