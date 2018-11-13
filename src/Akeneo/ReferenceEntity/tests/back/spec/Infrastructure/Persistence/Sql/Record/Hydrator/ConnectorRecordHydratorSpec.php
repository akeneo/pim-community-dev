<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\ConnectorRecordHydrator;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\RecordConnectorValueTransformer;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\TextConnectorValueTransformer;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerRegistry;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class ConnectorRecordHydratorSpec extends ObjectBehavior
{
    function let(
        Connection $connection
    ) {
        $valueTransformerRegistry = new ConnectorValueTransformerRegistry([
            new TextConnectorValueTransformer(),
            new RecordConnectorValueTransformer(),
        ]);

        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection, $valueTransformerRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorRecordHydrator::class);
    }

    function it_hydrates_a_connector_record(
        TextAttribute $nameAttribute,
        RecordAttribute $countryAttribute
    ) {
        $valueKeyCollection = ValueKeyCollection::fromValueKeys([
            ValueKey::createFromNormalized('name_designer_fingerprint_ecommerce_en_US'),
            ValueKey::createFromNormalized('name_designer_fingerprint_ecommerce_fr_FR'),
            ValueKey::createFromNormalized('country_designer_fingerprint'),
        ]);

        $attributes = [
            'name_designer_fingerprint'  => $nameAttribute,
            'country_designer_fingerprint' => $countryAttribute
        ];

        $nameAttribute->getCode()->willReturn(AttributeCode::fromString('name'));
        $countryAttribute->getCode()->willReturn(AttributeCode::fromString('country'));

        $row = [
            'identifier'                  => 'designer_starck_fingerprint',
            'code'                        => 'starck',
            'reference_entity_identifier' => 'designer',
            'image_file_key'              => 'test/image_1.jpg',
            'image_original_filename'     => 'image_1.jpg',
            'labels'                      => json_encode([
                'en_US' => 'Starck',
                'fr_FR' => 'Starck',
            ]),
            'value_collection'            => json_encode([
                'name_designer_fingerprint_ecommerce_en_US' => [
                    'data'      => 'Starck',
                    'locale'    => 'en_us',
                    'channel'   => 'ecommerce',
                    'attribute' => 'name_designer_fingerprint',
                ],
                'name_designer_fingerprint_ecommerce_fr_FR' => [
                    'data'      => 'Starck',
                    'locale'    => 'fr_FR',
                    'channel'   => 'ecommerce',
                    'attribute' => 'name_designer_fingerprint',
                ],
                'country_designer_fingerprint'              => [
                    'data'      => 'france',
                    'locale'    => null,
                    'channel'   => null,
                    'attribute' => 'country_designer_fingerprint',
                ]
            ])
        ];

        $file = new FileInfo();
        $file->setKey('test/image_1.jpg');
        $file->setOriginalFilename('image_1.jpg');
        $image = Image::fromFileInfo($file);

        $expectedRecord = $connectorRecord = new ConnectorRecord(
            RecordCode::fromString('starck'),
            LabelCollection::fromArray([
                'en_US' => 'Starck',
                'fr_FR' => 'Starck',
            ]),
            $image,
            [
                'name' => [
                    [
                        'locale'  => 'en_us',
                        'channel' => 'ecommerce',
                        'data'    => 'Starck',
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => 'ecommerce',
                        'data'    => 'Starck',
                    ]
                ],
                'country' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'france',
                    ]
                ]
            ]
        );

        $this->hydrate($row, $valueKeyCollection, $attributes)->shouldBeLike($expectedRecord);
    }

    function it_does_not_hydrates_unexpected_values(
        TextAttribute $nameAttribute,
        RecordAttribute $countryAttribute
    ) {
        $valueKeyCollection = ValueKeyCollection::fromValueKeys([
            ValueKey::createFromNormalized('name_designer_fingerprint_ecommerce_en_US'),
            ValueKey::createFromNormalized('name_designer_fingerprint_ecommerce_fr_FR'),
            ValueKey::createFromNormalized('country_designer_fingerprint'),
        ]);

        $attributes = [
            'name_designer_fingerprint'  => $nameAttribute,
            'country_designer_fingerprint' => $countryAttribute
        ];

        $nameAttribute->getCode()->willReturn(AttributeCode::fromString('name'));
        $countryAttribute->getCode()->willReturn(AttributeCode::fromString('country'));

        $row = [
            'identifier'                  => 'designer_starck_fingerprint',
            'code'                        => 'starck',
            'reference_entity_identifier' => 'designer',
            'image_file_key'              => null,
            'image_original_filename'     => null,
            'labels'                      => json_encode([
                'en_US' => 'Starck',
                'fr_FR' => 'Starck',
            ]),
            'value_collection'            => json_encode([
                'name_designer_fingerprint_ecommerce_en_US' => [
                    'data'      => 'Starck',
                    'locale'    => 'en_us',
                    'channel'   => 'ecommerce',
                    'attribute' => 'name_designer_fingerprint',
                ],
                'name_designer_fingerprint_ecommerce_fr_FR' => [
                    'data'      => 'Starck',
                    'locale'    => 'fr_FR',
                    'channel'   => 'ecommerce',
                    'attribute' => 'name_designer_fingerprint',
                ],
                'country_designer_fingerprint'              => [
                    'data'      => 'france',
                    'locale'    => null,
                    'channel'   => null,
                    'attribute' => 'country_designer_fingerprint',
                ],
                'description_designer_fingerprint_ecommerce_en_US' => [
                    'data'      => 'The famous french designer',
                    'locale'    => 'en_us',
                    'channel'   => 'ecommerce',
                    'attribute' => 'description_designer_fingerprint',
                ]
            ])
        ];

        $expectedRecord = $connectorRecord = new ConnectorRecord(
            RecordCode::fromString('starck'),
            LabelCollection::fromArray([
                'en_US' => 'Starck',
                'fr_FR' => 'Starck',
            ]),
            Image::createEmpty(),
            [
                'name' => [
                    [
                        'locale'  => 'en_us',
                        'channel' => 'ecommerce',
                        'data'    => 'Starck',
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => 'ecommerce',
                        'data'    => 'Starck',
                    ]
                ],
                'country' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'france',
                    ]
                ]
            ]
        );

        $this->hydrate($row, $valueKeyCollection, $attributes)->shouldBeLike($expectedRecord);
    }
}
