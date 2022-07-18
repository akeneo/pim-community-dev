<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementFamilyCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\AclMeasureConverter;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Indexing\GetTableValuesProjection;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class GetTableValuesProjectionSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository, AclMeasureConverter $measureConverter)
    {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                MeasurementColumn::fromNormalized([
                    'id' => ColumnIdGenerator::duration(),
                    'code' => 'duration',
                    'measurement_family_code' => 'Duration',
                    'measurement_default_unit_code' => 'second',
                ]),
            ])
        );
        $tableConfigurationRepository->getByAttributeCode('packaging')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::parcel(), 'code' => 'parcel', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::width(), 'code' => 'width']),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::length(), 'code' => 'length']),
            ])
        );

        $this->beConstructedWith($tableConfigurationRepository, $measureConverter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetTableValuesProjection::class);
    }

    function it_returns_an_empty_array_when_no_raw_values_are_provided()
    {
        $this->fromProductUuids([], ['value_collections' => []])->shouldReturn([]);
        $this->fromProductModelCodes([], ['value_collections' => []])->shouldReturn([]);
    }

    function it_normalizes_table_values_from_product_identifiers(AclMeasureConverter $measureConverter)
    {
        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();
        $valueCollections = [
            $uuid1 => new ReadValueCollection([
                TableValue::value(
                    'nutrition',
                    Table::fromNormalized([
                        [
                            ColumnIdGenerator::ingredient() => 'salt',
                            ColumnIdGenerator::quantity() => 10,
                            ColumnIdGenerator::duration() => ['amount' => 1, 'unit' => 'day'],
                        ],
                    ])
                ),
                TableValue::localizableValue(
                    'packaging',
                    Table::fromNormalized([
                        [
                            ColumnIdGenerator::parcel() => 'parcel_1',
                            ColumnIdGenerator::width() => 100,
                        ],
                    ]),
                    'en_US'
                ),
            ]),
            $uuid2 => new ReadValueCollection([
                TableValue::localizableValue(
                    'packaging',
                    Table::fromNormalized([
                        [
                            ColumnIdGenerator::parcel() => 'parcel_1',
                            ColumnIdGenerator::width() => 100,
                            ColumnIdGenerator::length() => 150,
                        ],
                        [
                            ColumnIdGenerator::parcel() => 'parcel_2',
                        ],
                    ]),
                    'en_US'
                ),
                TableValue::localizableValue(
                    'packaging',
                    Table::fromNormalized([
                        [
                            ColumnIdGenerator::parcel() => 'parcel_1',
                        ],
                    ]),
                    'fr_FR'
                ),
            ]),
        ];

        $measureConverter->convertAmountInStandardUnit(MeasurementFamilyCode::fromString('Duration'), '1', 'day')
            ->willReturn('86400');

        $this->fromProductUuids([], ['value_collections' => $valueCollections])->shouldReturn([
            $uuid1 => [
                'table_values' => [
                    'nutrition' => [
                        [
                            'row' => 'salt',
                            'column' => 'ingredient',
                            'value-select' => 'salt',
                            'is_column_complete' => true,
                        ],
                        [
                            'row' => 'salt',
                            'column' => 'quantity',
                            'value-number' => 10,
                            'is_column_complete' => true,
                        ],
                        [
                            'row' => 'salt',
                            'column' => 'duration',
                            'value-measurement' => '86400',
                            'is_column_complete' => true,
                        ],
                    ],
                    'packaging' => [
                        [
                            'row' => 'parcel_1',
                            'column' => 'parcel',
                            'value-select' => 'parcel_1',
                            'is_column_complete' => true,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_1',
                            'column' => 'width',
                            'value-number' => 100,
                            'is_column_complete' => true,
                            'locale' => 'en_US',
                        ],
                    ],
                ],
            ],
            $uuid2 => [
                'table_values' => [
                    'packaging' => [
                        [
                            'row' => 'parcel_1',
                            'column' => 'parcel',
                            'value-select' => 'parcel_1',
                            'is_column_complete' => true,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_1',
                            'column' => 'width',
                            'value-number' => 100,
                            'is_column_complete' => false,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_1',
                            'column' => 'length',
                            'value-number' => 150,
                            'is_column_complete' => false,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_2',
                            'column' => 'parcel',
                            'value-select' => 'parcel_2',
                            'is_column_complete' => true,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_1',
                            'column' => 'parcel',
                            'value-select' => 'parcel_1',
                            'is_column_complete' => true,
                            'locale' => 'fr_FR',
                        ],
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_table_values_from_product_model_codes()
    {
        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();
        $valueCollections = [
            $uuid1 => new ReadValueCollection([
                TableValue::value(
                    'nutrition',
                    Table::fromNormalized([
                        [
                            ColumnIdGenerator::ingredient() => 'salt',
                            ColumnIdGenerator::quantity() => 10,
                        ],
                    ])
                ),
                TableValue::localizableValue(
                    'packaging',
                    Table::fromNormalized([
                        [
                            ColumnIdGenerator::parcel() => 'parcel_1',
                            ColumnIdGenerator::width() => 100,
                        ],
                    ]),
                    'en_US'
                ),
            ]),
            $uuid2 => new ReadValueCollection([
                TableValue::localizableValue(
                    'packaging',
                    Table::fromNormalized([
                        [
                            ColumnIdGenerator::parcel() => 'parcel_1',
                            ColumnIdGenerator::width() => 100,
                            ColumnIdGenerator::length() => 150,
                        ],
                        [
                            ColumnIdGenerator::parcel() => 'parcel_2',
                        ],
                    ]),
                    'en_US'
                ),
                TableValue::localizableValue(
                    'packaging',
                    Table::fromNormalized([
                        [
                            ColumnIdGenerator::parcel() => 'parcel_1',
                        ],
                    ]),
                    'fr_FR'
                ),
            ]),
        ];

        $this->fromProductModelCodes([], ['value_collections' => $valueCollections])->shouldReturn([
            $uuid1 => [
                'table_values' => [
                    'nutrition' => [
                        [
                            'row' => 'salt',
                            'column' => 'ingredient',
                            'value-select' => 'salt',
                            'is_column_complete' => true,
                        ],
                        [
                            'row' => 'salt',
                            'column' => 'quantity',
                            'value-number' => 10,
                            'is_column_complete' => true,
                        ],
                    ],
                    'packaging' => [
                        [
                            'row' => 'parcel_1',
                            'column' => 'parcel',
                            'value-select' => 'parcel_1',
                            'is_column_complete' => true,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_1',
                            'column' => 'width',
                            'value-number' => 100,
                            'is_column_complete' => true,
                            'locale' => 'en_US',
                        ],
                    ],
                ],
            ],
            $uuid2 => [
                'table_values' => [
                    'packaging' => [
                        [
                            'row' => 'parcel_1',
                            'column' => 'parcel',
                            'value-select' => 'parcel_1',
                            'is_column_complete' => true,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_1',
                            'column' => 'width',
                            'value-number' => 100,
                            'is_column_complete' => false,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_1',
                            'column' => 'length',
                            'value-number' => 150,
                            'is_column_complete' => false,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_2',
                            'column' => 'parcel',
                            'value-select' => 'parcel_2',
                            'is_column_complete' => true,
                            'locale' => 'en_US',
                        ],
                        [
                            'row' => 'parcel_1',
                            'column' => 'parcel',
                            'value-select' => 'parcel_1',
                            'is_column_complete' => true,
                            'locale' => 'fr_FR',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
