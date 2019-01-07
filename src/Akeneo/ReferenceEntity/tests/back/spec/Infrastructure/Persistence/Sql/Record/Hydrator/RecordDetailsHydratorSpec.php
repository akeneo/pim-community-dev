<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDetailsHydratorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\ValueHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class RecordDetailsHydratorSpec extends ObjectBehavior
{
    public function let(ValueHydratorInterface $valueHydrator, Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection, $valueHydrator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordDetailsHydratorInterface::class);
    }

    public function it_hydrates_a_record_details() {
        $labels = [
            'en_US' => 'World of Warcraft',
            'fr_FR' => 'World of Warcraft',
        ];

        $recordDetails = $this->hydrate(
            [
                'identifier'                 => 'wow_game_A8E76F8A76E87F6A',
                'code'                       => 'world_of_warcraft',
                'reference_entity_identifier' => 'game',
                'labels'                     => json_encode($labels),
                'value_collection'           => json_encode([]),
            ],
            []
        );

        $recordDetails->normalize()->shouldReturn([
            'identifier' => 'wow_game_A8E76F8A76E87F6A',
            'reference_entity_identifier' => 'game',
            'code' => 'world_of_warcraft',
            'labels' => $labels,
            'image' => null,
            'values' => [],
            'permission' => [
                'edit' => true
            ]
        ]);
    }

    public function it_hydrates_a_record_details_with_values(
        TextAttribute $gameDescription,
        AttributeIdentifier $gameDescriptionIdentifier
    ) {
        $gameDescriptionIdentifier->normalize()->willReturn('description_game_fingerprint');
        $gameDescription->getIdentifier()->willReturn($gameDescriptionIdentifier);

        $gameDescriptionFrFrNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'Le fameux MMORPG PC de Blizzard',
        ];
        $gameDescriptionEnUSNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'en_US',
            'data'      => 'The famous MMORPG PC Game by Blizzard',
        ];
        $gameBoxImageMobileNormalized = [
            'attribute' => 'boximage_game_fingerprint',
            'channel'   => 'mobile',
            'locale'    => null,
            'data'      => [
                'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                'original_filename' => 'box_wow.png',
            ],
        ];

        $emptyValues = [
            'description_game_fingerprint-fr_FR' => [
                'attribute' => [],
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => null
            ],
            'description_game_fingerprint-en_US' => [
                'attribute' => [],
                'channel' => null,
                'locale' => 'en_US',
                'data' => null
            ],
            'boximage_game_fingerprint-mobile' => [
                'attribute' => [],
                'channel' => 'mobile',
                'locale' => null,
                'data' => null
            ],
        ];

        $rawValues = [
            'description_game_fingerprint-fr_FR' => $gameDescriptionFrFrNormalized,
            'description_game_fingerprint-en_US' => $gameDescriptionEnUSNormalized,
            'boximage_game_fingerprint-mobile' => $gameBoxImageMobileNormalized,
        ];

        $expectedValues = [
            [
                'attribute' => [],
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'Le fameux MMORPG PC de Blizzard',
            ],
            [
                'attribute' => [],
                'channel' => null,
                'locale' => 'en_US',
                'data' => 'The famous MMORPG PC Game by Blizzard',
            ],
            [
                'attribute' => [],
                'channel' => 'mobile',
                'locale' => null,
                'data' => [
                    'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                    'original_filename' => 'box_wow.png',
                ],
            ],
        ];

        $recordDetails = $this->hydrate(
            [
                'identifier' => 'wow_game_A8E76F8A76E87F6A',
                'code' => 'world_of_warcraft',
                'reference_entity_identifier' => 'game',
                'labels' => json_encode([]),
                'value_collection' => json_encode($rawValues),
            ],
            $emptyValues
        );

        $recordDetails->normalize()['values']->shouldBe($expectedValues);
    }

    public function it_does_not_keep_unexpected_values()
    {
        $gameDescriptionFrFrNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'Le fameux MMORPG PC de Blizzard',
        ];

        $rawValues = [
            'description_game_fingerprint-fr_FR'    => $gameDescriptionFrFrNormalized,
            'unknown_attribute1-fingerprint'    => [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'The famous MMORPG PC Game by Blizzard',
            ],
            'unknown_attribute2-fingerprint' => [
                'attribute' => 'boximage_game_fingerprint',
                'channel'   => 'mobile',
                'locale'    => null,
                'data'      => [
                    'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                    'original_filename' => 'box_wow.png',
                ],
            ],
        ];

        $emptyValues = [
            'description_game_fingerprint-fr_FR' => [
                'attribute' => [],
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => null
            ],
            'description_game_fingerprint-en_US' => [
                'attribute' => [],
                'channel' => null,
                'locale' => 'en_US',
                'data' => null
            ],
        ];

        $expectedValues = [
            [
                'attribute' => [],
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'Le fameux MMORPG PC de Blizzard'
            ],
            [
                'attribute' => [],
                'channel' => null,
                'locale' => 'en_US',
                'data' => null
            ],
        ];

        $record = $this->hydrate(
            [
                'identifier'                 => 'wow_game_A8E76F8A76E87F6A',
                'code'                       => 'world_of_warcraft',
                'reference_entity_identifier' => 'game',
                'labels'                     => json_encode([]),
                'value_collection'           => json_encode($rawValues),
            ],
            $emptyValues
        );

        $record->normalize()['values']->shouldBe($expectedValues);
    }
}
