<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDetailsHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class RecordDetailsHydratorSpec extends ObjectBehavior
{
    public function let(
        Connection $connection,
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection, $findCodesByIdentifiers, $findValueKeysByAttributeType);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordDetailsHydratorInterface::class);
    }

    public function it_hydrates_a_record_details(FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType)
    {
        $findValueKeysByAttributeType->find(
            ReferenceEntityIdentifier::fromString('game'),
            ['record', 'record_collection']
        )->willReturn([]);

        $recordDetails = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'reference_entity_identifier' => 'game',
                'value_collection'            => json_encode([
                    'label_game_fingerprint_fr_FR' => [
                        'data'      => 'World of Warcraft',
                        'channel'   => null,
                        'locale'    => 'fr_FR',
                        'attribute' => 'label_game_fingerprint',
                    ],
                    'label_game_fingerprint_en_US' => [
                        'data'      => 'World of Warcraft',
                        'channel'   => null,
                        'locale'    => 'en_US',
                        'attribute' => 'label_game_fingerprint',
                    ],
                ]),
                'attribute_as_label'          => 'label_game_fingerprint',
                'attribute_as_image'          => 'main_image_game_fingerprint',
            ],
            [
                'label_game_fingerprint_fr_FR' => [
                    'data'      => null,
                    'channel'   => null,
                    'locale'    => 'fr_FR',
                    'attribute' => 'label_game_fingerprint',
                ],
                'label_game_fingerprint_en_US' => [
                    'data'      => null,
                    'channel'   => null,
                    'locale'    => 'en_US',
                    'attribute' => 'label_game_fingerprint',
                ],
            ]
        );

        $recordDetails->normalize()->shouldReturn([
            'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
            'reference_entity_identifier' => 'game',
            'code'                        => 'world_of_warcraft',
            'labels'                      => [
                'fr_FR' => 'World of Warcraft',
                'en_US' => 'World of Warcraft',
            ],
            'image'                       => null,
            'values'                      => [
                [
                    'data'      => 'World of Warcraft',
                    'channel'   => null,
                    'locale'    => 'fr_FR',
                    'attribute' => 'label_game_fingerprint',
                ],
                [
                    'data'      => 'World of Warcraft',
                    'channel'   => null,
                    'locale'    => 'en_US',
                    'attribute' => 'label_game_fingerprint',
                ],
            ],
            'permission'                  => [
                'edit' => true,
            ],
        ]);
    }

    public function it_hydrates_a_record_details_with_values(
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        TextAttribute $gameDescription,
        AttributeIdentifier $gameDescriptionIdentifier
    ) {
        $findValueKeysByAttributeType->find(
            ReferenceEntityIdentifier::fromString('game'),
            ['record', 'record_collection']
        )->willReturn([]);

        $gameDescriptionIdentifier->normalize()->willReturn('description_game_fingerprint');
        $gameDescription->getIdentifier()->willReturn($gameDescriptionIdentifier);

        $emptyValues = [
            'description_game_fingerprint-fr_FR' => [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => null,
            ],
            'description_game_fingerprint-en_US' => [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => null,
            ],
            'boximage_game_fingerprint-mobile'   => [
                'attribute' => [
                    'identifier' => 'boximage_game_fingerprint',
                ],
                'channel'   => 'mobile',
                'locale'    => null,
                'data'      => null,
            ],
        ];

        $rawValues = [
            'description_game_fingerprint-fr_FR' => [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => 'Le fameux MMORPG PC de Blizzard',
            ],
            'description_game_fingerprint-en_US' => [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'The famous MMORPG PC Game by Blizzard',
            ],
            'boximage_game_fingerprint-mobile'   => [
                'attribute' => [
                    'identifier' => 'boximage_game_fingerprint',
                ],
                'channel'   => 'mobile',
                'locale'    => null,
                'data'      => [
                    'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                    'original_filename' => 'box_wow.png',
                ],
            ],
        ];

        $expectedValues = [
            [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => 'Le fameux MMORPG PC de Blizzard',
            ],
            [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'The famous MMORPG PC Game by Blizzard',
            ],
            [
                'attribute' => [
                    'identifier' => 'boximage_game_fingerprint',
                ],
                'channel'   => 'mobile',
                'locale'    => null,
                'data'      => [
                    'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                    'original_filename' => 'box_wow.png',
                ],
            ],
        ];

        $recordDetails = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'reference_entity_identifier' => 'game',
                'value_collection'            => json_encode($rawValues),
                'attribute_as_label'          => 'another_attribute_game_fingerprint',
                'attribute_as_image'          => 'another_game_fingerprint',
            ],
            $emptyValues
        );

        $recordDetails->normalize()['values']->shouldBe($expectedValues);
    }

    public function it_does_not_keep_unexpected_values(FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType)
    {
        $findValueKeysByAttributeType->find(
            ReferenceEntityIdentifier::fromString('game'),
            ['record', 'record_collection']
        )->willReturn([]);

        $rawValues = [
            'description_game_fingerprint-fr_FR' => [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => 'Le fameux MMORPG PC de Blizzard',
            ],
            'unknown_attribute1-fingerprint'     => [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'The famous MMORPG PC Game by Blizzard',
            ],
            'unknown_attribute2-fingerprint'     => [
                'attribute' => [
                    'identifier' => 'boximage_game_fingerprint',
                ],
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
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => null,
            ],
            'description_game_fingerprint-en_US' => [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => null,
            ],
        ];

        $expectedValues = [
            [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => 'Le fameux MMORPG PC de Blizzard',
            ],
            [
                'attribute' => [
                    'identifier' => 'description_game_fingerprint',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => null,
            ],
        ];

        $record = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'reference_entity_identifier' => 'game',
                'value_collection'            => json_encode($rawValues),
                'attribute_as_label'          => 'another_attribute_game_fingerprint',
                'attribute_as_image'          => 'another_game_fingerprint',
            ],
            $emptyValues
        );

        $record->normalize()['values']->shouldBe($expectedValues);
    }

    // it replaces record identifiers by code if there is any value
    public function it_replaces_record_identifiers_by_code_if_there_is_any_value(
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType
    ) {
        $emptyValues = [
            'developers_fingerprint' => [
                'attribute' => [
                    'identifier' => 'developers_fingerprint',
                ],
                'channel' => null,
                'locale' => null,
                'data' => []
            ],
            'editor_fingerprint' => [
                'attribute' => [
                    'identifier' => 'editor_fingerprint',
                ],
                'channel' => null,
                'locale' => null,
                'data' => null
            ]
        ];

        $rawValues = [
            'developers_fingerprint' => [
                'attribute' => [
                    'identifier' => 'developers_fingerprint',
                ],
                'channel' => null,
                'locale' => null,
                'data' => ['super_studio_identifier', 'wizard_games_identifier']
            ],
            'editor_fingerprint' => [
                'attribute' => [
                    'identifier' => 'editor_fingerprint',
                ],
                'channel' => null,
                'locale' => null,
                'data' => 'runic_identifier'
            ]
        ];

        $expectedValues = [
            [
                'attribute' => [
                    'identifier' => 'developers_fingerprint',
                ],
                'channel' => null,
                'locale' => null,
                'data' => ['super_studio', 'wizard_games']
            ],
            [
                'attribute' => [
                    'identifier' => 'editor_fingerprint',
                ],
                'channel' => null,
                'locale' => null,
                'data' => 'runic'
            ]
        ];

        $findValueKeysByAttributeType->find(
            ReferenceEntityIdentifier::fromString('game'),
            ['record', 'record_collection']
        )->willReturn([
            'developers_fingerprint',
            'editor_fingerprint',
        ]);

        $findCodesByIdentifiers->find([
            'super_studio_identifier',
            'wizard_games_identifier',
            'runic_identifier',
        ])->willReturn([
            'super_studio_identifier' => 'super_studio',
            'wizard_games_identifier' => 'wizard_games',
            'runic_identifier' => 'runic',
        ]);

        $recordDetails = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'reference_entity_identifier' => 'game',
                'value_collection'            => json_encode($rawValues),
                'attribute_as_label'          => 'another_attribute_game_fingerprint',
                'attribute_as_image'          => 'another_game_fingerprint',
            ],
            $emptyValues
        );

        $recordDetails->normalize()['values']->shouldBe($expectedValues);
    }
}
