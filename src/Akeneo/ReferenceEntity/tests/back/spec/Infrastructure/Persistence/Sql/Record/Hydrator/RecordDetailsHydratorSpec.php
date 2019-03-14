<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindValueKeyCollectionForAttributeType;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlGetRecordTypeForRecordLinkAttribute;
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
        SqlFindValueKeyCollectionForAttributeType $findValueKeyCollectionForAttributeType,
        SqlGetRecordTypeForRecordLinkAttribute $getRecordTypeForRecordLinkAttribute,
        SqlRecordsExists $recordsExists
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith(
            $connection,
            $findValueKeyCollectionForAttributeType,
            $getRecordTypeForRecordLinkAttribute,
            $recordsExists
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordDetailsHydratorInterface::class);
    }

    public function it_hydrates_a_record_details(SqlFindValueKeyCollectionForAttributeType $findValueKeyCollectionForAttributeType)
    {
        $findValueKeyCollectionForAttributeType->fetch(
            Argument::type(ReferenceEntityIdentifier::class),
            'record'
        )->willReturn(ValueKeyCollection::fromValueKeys([]));
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
        SqlFindValueKeyCollectionForAttributeType $findValueKeyCollectionForAttributeType
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

        $findValueKeyCollectionForAttributeType->fetch(
            Argument::type(ReferenceEntityIdentifier::class),
            'record'
        )->willReturn(ValueKeyCollection::fromValueKeys([]));
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

    public function it_does_not_keep_unexpected_values(
        SqlFindValueKeyCollectionForAttributeType $findValueKeyCollectionForAttributeType
    ) {
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

        $findValueKeyCollectionForAttributeType->fetch(
            Argument::type(ReferenceEntityIdentifier::class),
            'record'
        )->willReturn(ValueKeyCollection::fromValueKeys([]));
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
        SqlFindValueKeyCollectionForAttributeType $findValueKeyCollectionForAttributeType,
        SqlGetRecordTypeForRecordLinkAttribute $getRecordTypeForRecordLinkAttribute,
        SqlRecordsExists $recordsExists
    ) {
        $rawValues = [
            'simple_record_link' => [
                'attribute' => [
                    'identifier' => 'main_designer_brand',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => 'stark',
            ]
        ];

        $emptyValues = [
            'simple_record_link' => [
                'attribute' => [
                    'identifier' => 'main_designer_brand',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => null,
            ],
        ];

        $findValueKeyCollectionForAttributeType->fetch(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'reference_entity' === $referenceEntityIdentifier->normalize();
            }),
            'record'
        )->willReturn(
            ValueKeyCollection::fromValueKeys(
                [ValueKey::createFromNormalized('simple_record_link')]
            )
        );
        $getRecordTypeForRecordLinkAttribute->fetch('main_designer_brand')->willReturn('reference_entity');
        $recordsExists->withReferenceEntityAndCodes(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'reference_entity' === $referenceEntityIdentifier->normalize();
            }),
            ['stark']
        )->willReturn([]);

        $record = $this->hydrate(
            [
                'identifier'                  => 'a_record_with_simple_record_links-fingerprint',
                'code'                        => 'a_record_with_simple_record_links',
                'reference_entity_identifier' => 'reference_entity',
                'value_collection'            => json_encode($rawValues),
                'attribute_as_label'          => 'localizable_simple_record_link',
                'attribute_as_image'          => 'simple_record_link',
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
                    'data'      => null,
                ],
            ]
        );
    }

    public function it_does_not_keep_broken_multiple_record_links(
        SqlFindValueKeyCollectionForAttributeType $findValueKeyCollectionForAttributeType,
        SqlGetRecordTypeForRecordLinkAttribute $getRecordTypeForRecordLinkAttribute,
        SqlRecordsExists $recordsExists
    ) {
        $rawValues = [
            'multiple_record_link' => [
                'attribute' => [
                    'identifier' => 'main_brand_designer',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => ['dyson', 'muuto'],
            ]
        ];

        $emptyValues = [
            'multiple_record_link' => [
                'attribute' => [
                    'identifier' => 'main_brand_designer',
                ],
                'channel'   => null,
                'locale'    => null,
                'data'      => null,
            ],
        ];

        $findValueKeyCollectionForAttributeType->fetch(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'reference_entity' === $referenceEntityIdentifier->normalize();
            }),
            'record'
        )->willReturn(
            ValueKeyCollection::fromValueKeys(
                [ValueKey::createFromNormalized('multiple_record_link')]
            )
        );
        $getRecordTypeForRecordLinkAttribute->fetch('main_brand_designer')->willReturn('reference_entity');
        $recordsExists->withReferenceEntityAndCodes(
            Argument::that(function (ReferenceEntityIdentifier $referenceEntityIdentifier) {
                return 'reference_entity' === $referenceEntityIdentifier->normalize();
            }),
            ['dyson', 'muuto']
        )->willReturn([]);

        $record = $this->hydrate(
            [
                'identifier'                  => 'a_record_with_simple_record_links-fingerprint',
                'code'                        => 'a_record_with_simple_record_links',
                'reference_entity_identifier' => 'reference_entity',
                'value_collection'            => json_encode($rawValues),
                'attribute_as_label'          => 'localizable_simple_record_link',
                'attribute_as_image'          => 'simple_record_link',
            ],
            $emptyValues
        );

        $record->normalize()['values']->shouldBe([
                [
                    'attribute' => [
                        'identifier' => 'main_brand_designer',
                    ],
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => null,
                ],
            ]
        );
    }
}
