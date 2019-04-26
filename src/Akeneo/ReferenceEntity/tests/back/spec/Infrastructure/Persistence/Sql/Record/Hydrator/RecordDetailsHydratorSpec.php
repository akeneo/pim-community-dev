<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindRecordLinkValueKeys;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDetailsHydratorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlRecordsExists;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RecordDetailsHydratorSpec extends ObjectBehavior
{
    public function let(
        Connection $connection,
        SqlRecordsExists $recordsExists,
        SqlFindRecordLinkValueKeys $findRecordLinkValueKeys
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith(
            $connection,
            $recordsExists,
            $findRecordLinkValueKeys
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordDetailsHydratorInterface::class);
    }

    public function it_hydrates_a_record_details(SqlFindRecordLinkValueKeys $findRecordLinkValueKeys)
    {
        $findRecordLinkValueKeys->fetch(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn([]);

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
        TextAttribute $gameDescription,
        AttributeIdentifier $gameDescriptionIdentifier,
        SqlFindRecordLinkValueKeys $findRecordLinkValueKeys
    ) {
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

        $findRecordLinkValueKeys->fetch(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn([]);

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

    public function it_does_not_keep_unexpected_values(SqlFindRecordLinkValueKeys $findRecordLinkValueKeys)
    {
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

        $findRecordLinkValueKeys->fetch(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn([]);

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

    public function it_does_not_keep_broken_simple_record_links(
        SqlFindRecordLinkValueKeys $findRecordLinkValueKeys,
        SqlRecordsExists $recordsExists
    ) {
        $rawValues = [
            'main_designer_brand' => [
                'attribute' => [
                    'identifier' => 'main_designer_brand',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => 'stark',
            ],
            'city_brand-fr_FR' => [
                'attribute' => [
                    'identifier' => 'city_brand',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => 'nantes',
            ],
            'city_brand-en_US' => [
                'attribute' => [
                    'identifier' => 'city_brand',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'boston',
            ]
        ];

        $emptyValues = [
            'main_designer_brand' => [
                'attribute' => [
                    'identifier' => 'main_designer_brand',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => null,
            ],
            'city_brand-fr_FR' => [
                'attribute' => [
                    'identifier' => 'city_brand',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => null,
            ],
            'city_brand-en_US' => [
                'attribute' => [
                    'identifier' => 'city_brand',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => null,
            ],
            'city_brand-de_DE' => [
                'attribute' => [
                    'identifier' => 'city_brand',
                ],
                'channel'   => null,
                'locale'    => 'de_DE',
                'data'      => null,
            ]
        ];

        $findRecordLinkValueKeys->fetch(Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
            return 'brand' === $referenceEntityIdentifier->normalize();
        }))->willReturn([
            [
                'value_key' => 'main_designer_brand',
                'attribute_identifier' => 'main_designer_brand',
                'record_type' => 'designer',
                'attribute_type' => 'record',
            ],
            [
                'value_key' => 'city_brand-fr_FR',
                'attribute_identifier' => 'city_brand',
                'record_type' => 'city',
                'attribute_type' => 'record',
            ],
            [
                'value_key' => 'city_brand-en_US',
                'attribute_identifier' => 'city_brand',
                'record_type' => 'city',
                'attribute_type' => 'record',
            ],
            [
                'value_key' => 'city_brand-de_DE',
                'attribute_identifier' => 'city_brand',
                'record_type' => 'city',
                'attribute_type' => 'record',
            ]
        ]);

        $recordsExists->withReferenceEntityAndCodes(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'designer' === $referenceEntityIdentifier->normalize();
            }),
            ['stark']
        )->willReturn(['stark']);

        $recordsExists->withReferenceEntityAndCodes(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'city' === $referenceEntityIdentifier->normalize();
            }),
            ['nantes']
        )->willReturn([]);

        $recordsExists->withReferenceEntityAndCodes(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'city' === $referenceEntityIdentifier->normalize();
            }),
            ['boston']
        )->willReturn(['boston']);

        $record = $this->hydrate(
            [
                'identifier'                  => 'nike-abcdef123456789',
                'code'                        => 'nike',
                'reference_entity_identifier' => 'brand',
                'value_collection'            => json_encode($rawValues),
                'attribute_as_label'          => 'label',
                'attribute_as_image'          => 'image',
            ],
            $emptyValues
        );

        $record->normalize()['values']->shouldBe([
            [
                'attribute' => [
                    'identifier' => 'main_designer_brand',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => 'stark',
            ],
            [
                'attribute' => [
                    'identifier' => 'city_brand',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => null,
            ],
            [
                'attribute' => [
                    'identifier' => 'city_brand',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'boston',
            ],
            [
                'attribute' => [
                    'identifier' => 'city_brand',
                ],
                'channel'   => null,
                'locale'    => 'de_DE',
                'data'      => null,
            ]
        ]);
    }

    public function it_does_not_keep_broken_multiple_record_links(
        SqlFindRecordLinkValueKeys $findRecordLinkValueKeys,
        SqlRecordsExists $recordsExists
    ) {
        $rawValues = [
            'main_designers_brand' => [
                'attribute' => [
                    'identifier' => 'main_designers_brand',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => ['stark', 'coco'],
            ],
            'cities_brand-fr_FR' => [
                'attribute' => [
                    'identifier' => 'cities_brand',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => ['nantes', 'paris', 'lyon'],
            ],
            'cities_brand-en_US' => [
                'attribute' => [
                    'identifier' => 'cities_brand',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => ['boston', 'newyork'],
            ]
        ];

        $emptyValues = [
            'main_designers_brand' => [
                'attribute' => [
                    'identifier' => 'main_designers_brand',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => null,
            ],
            'cities_brand-fr_FR' => [
                'attribute' => [
                    'identifier' => 'cities_brand',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => null,
            ],
            'cities_brand-en_US' => [
                'attribute' => [
                    'identifier' => 'cities_brand',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => null,
            ],
            'cities_brand-de_DE' => [
                'attribute' => [
                    'identifier' => 'cities_brand',
                ],
                'channel'   => null,
                'locale'    => 'de_DE',
                'data'      => null,
            ]
        ];

        $findRecordLinkValueKeys->fetch(Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
            return 'brand' === $referenceEntityIdentifier->normalize();
        }))->willReturn([
            [
                'value_key' => 'main_designers_brand',
                'attribute_identifier' => 'main_designers_brand',
                'record_type' => 'designer',
                'attribute_type' => 'record_collection',
            ],
            [
                'value_key' => 'cities_brand-fr_FR',
                'attribute_identifier' => 'cities_brand',
                'record_type' => 'city',
                'attribute_type' => 'record_collection',
            ],
            [
                'value_key' => 'cities_brand-en_US',
                'attribute_identifier' => 'cities_brand',
                'record_type' => 'city',
                'attribute_type' => 'record_collection',
            ],
            [
                'value_key' => 'cities_brand-de_DE',
                'attribute_identifier' => 'cities_brand',
                'record_type' => 'city',
                'attribute_type' => 'record_collection',
            ]
        ]);

        $recordsExists->withReferenceEntityAndCodes(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'designer' === $referenceEntityIdentifier->normalize();
            }),
            ['stark', 'coco']
        )->willReturn(['stark']);

        $recordsExists->withReferenceEntityAndCodes(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'city' === $referenceEntityIdentifier->normalize();
            }),
            ['nantes', 'paris', 'lyon']
        )->willReturn(['nantes']);

        $recordsExists->withReferenceEntityAndCodes(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'city' === $referenceEntityIdentifier->normalize();
            }),
            ['boston', 'newyork']
        )->willReturn(['boston', 'newyork']);

        $record = $this->hydrate(
            [
                'identifier'                  => 'nike-abcdef123456789',
                'code'                        => 'nike',
                'reference_entity_identifier' => 'brand',
                'value_collection'            => json_encode($rawValues),
                'attribute_as_label'          => 'label',
                'attribute_as_image'          => 'image',
            ],
            $emptyValues
        );

        $record->normalize()['values']->shouldBe([
            [
                'attribute' => [
                    'identifier' => 'main_designers_brand',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => ['stark'],
            ],
            [
                'attribute' => [
                    'identifier' => 'cities_brand',
                ],
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => ['nantes'],
            ],
            [
                'attribute' => [
                    'identifier' => 'cities_brand',
                ],
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => ['boston', 'newyork'],
            ],
            [
                'attribute' => [
                    'identifier' => 'cities_brand',
                ],
                'channel'   => null,
                'locale'    => 'de_DE',
                'data'      => null,
            ]
        ]);
    }
}
