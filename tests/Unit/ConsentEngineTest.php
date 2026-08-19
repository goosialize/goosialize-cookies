<?php

declare(strict_types=1);

use Goosialize\Cookies\Consent\ConsentCategory;
use Goosialize\Cookies\Consent\ConsentDecision;
use Goosialize\Cookies\Consent\ConsentEngine;
use Goosialize\Cookies\Consent\ConsentSelection;
use Goosialize\Cookies\Consent\ConsentSerializer;
use Goosialize\Cookies\Consent\ConsentState;
use Goosialize\Cookies\Consent\ConsentValidator;
use Goosialize\Cookies\Consent\ConsentVersion;
use PHPUnit\Framework\TestCase;

final class ConsentEngineTest extends TestCase
{
    public function testAcceptAllGrantsEveryCategory(): void
    {
        $engine = new ConsentEngine(new ConsentVersion(1));

        $snapshot = $engine->decide(
            ConsentDecision::AcceptAll,
            recordedAt: new DateTimeImmutable(
                '2026-08-19T18:00:00+00:00'
            )
        );

        self::assertSame(
            ConsentState::AcceptedAll,
            $snapshot->state()
        );

        foreach (ConsentCategory::cases() as $category) {
            self::assertTrue($snapshot->granted($category));
        }
    }

    public function testRejectOptionalKeepsNecessaryOnly(): void
    {
        $engine = new ConsentEngine(new ConsentVersion(1));

        $snapshot = $engine->decide(
            ConsentDecision::RejectOptional
        );

        self::assertSame(
            ConsentState::RejectedOptional,
            $snapshot->state()
        );

        self::assertTrue(
            $snapshot->granted(ConsentCategory::Necessary)
        );

        self::assertFalse(
            $snapshot->granted(ConsentCategory::Preferences)
        );

        self::assertFalse(
            $snapshot->granted(ConsentCategory::Analytics)
        );

        self::assertFalse(
            $snapshot->granted(ConsentCategory::Marketing)
        );
    }

    public function testCustomCannotDisableNecessary(): void
    {
        $selection = ConsentSelection::custom([
            'necessary' => false,
            'preferences' => true,
            'analytics' => false,
            'marketing' => true,
        ]);

        self::assertTrue(
            $selection->granted(ConsentCategory::Necessary)
        );

        self::assertTrue(
            $selection->granted(ConsentCategory::Preferences)
        );

        self::assertFalse(
            $selection->granted(ConsentCategory::Analytics)
        );

        self::assertTrue(
            $selection->granted(ConsentCategory::Marketing)
        );
    }

    public function testUnknownCategoryIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ConsentSelection::custom([
            'analytics' => true,
            'mystery' => true,
        ]);
    }

    public function testNonBooleanCategoryIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ConsentSelection::custom([
            'analytics' => 1,
        ]);
    }

    public function testSerializationRoundTrip(): void
    {
        $version = new ConsentVersion(1);
        $engine = new ConsentEngine($version);

        $original = $engine->decide(
            ConsentDecision::Custom,
            [
                'preferences' => true,
                'analytics' => true,
                'marketing' => false,
            ],
            new DateTimeImmutable(
                '2026-08-19T18:00:00+00:00'
            )
        );

        $serializer = new ConsentSerializer();

        $encoded = $serializer->serialize($original);
        $restored = $serializer->deserialize($encoded, $version);

        self::assertNotNull($restored);
        self::assertSame(
            $original->version->value,
            $restored->version->value
        );
        self::assertSame(
            $original->selection->toArray(),
            $restored->selection->toArray()
        );
        self::assertSame(
            $original->recordedAt->format(DATE_ATOM),
            $restored->recordedAt->format(DATE_ATOM)
        );
    }

    public function testOldVersionIsInvalidated(): void
    {
        $payload = json_encode([
            'version' => 1,
            'timestamp' => '2026-08-19T18:00:00+00:00',
            'categories' => [
                'necessary' => true,
                'preferences' => false,
                'analytics' => true,
                'marketing' => false,
            ],
        ], JSON_THROW_ON_ERROR);

        $serializer = new ConsentSerializer();

        self::assertNull(
            $serializer->deserialize(
                $payload,
                new ConsentVersion(2)
            )
        );
    }

    public function testFutureVersionIsInvalidated(): void
    {
        $payload = json_encode([
            'version' => 3,
            'timestamp' => '2026-08-19T18:00:00+00:00',
            'categories' => [
                'necessary' => true,
                'preferences' => false,
                'analytics' => true,
                'marketing' => false,
            ],
        ], JSON_THROW_ON_ERROR);

        $serializer = new ConsentSerializer();

        self::assertNull(
            $serializer->deserialize(
                $payload,
                new ConsentVersion(2)
            )
        );
    }

    public function testMalformedPayloadIsRejected(): void
    {
        $serializer = new ConsentSerializer();

        self::assertNull(
            $serializer->deserialize(
                '{invalid-json',
                new ConsentVersion(1)
            )
        );
    }

    public function testNecessaryFalsePayloadIsRejected(): void
    {
        $validator = new ConsentValidator();

        self::assertFalse(
            $validator->isValidPayload(
                [
                    'version' => 1,
                    'timestamp' =>
                        '2026-08-19T18:00:00+00:00',
                    'categories' => [
                        'necessary' => false,
                        'preferences' => false,
                        'analytics' => false,
                        'marketing' => false,
                    ],
                ],
                new ConsentVersion(1)
            )
        );
    }
}
