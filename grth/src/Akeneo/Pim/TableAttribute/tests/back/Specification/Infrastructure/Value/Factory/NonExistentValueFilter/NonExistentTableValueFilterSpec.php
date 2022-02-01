<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory\NonExistentValueFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory\NonExistentValueFilter\NonExistentTableValueFilter;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class NonExistentTableValueFilterSpec extends ObjectBehavior
{
    const COLUMNID_RECORDBRAND = 'brand_d39d3c48-46e6-4744-8196-56e08563fd46';

    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        GetExistingRecordCodes $getExistingRecordCodes
    ) {
        $tableConfigurationRepository->getByAttributeCode('food_composition')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::supplier(), 'code' => 'supplier']),
                ReferenceEntityColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'origin', 'reference_entity_identifier' => 'record']),
                ReferenceEntityColumn::fromNormalized(['id' => self::COLUMNID_RECORDBRAND, 'code' => 'brand', 'reference_entity_identifier' => 'brand']),
            ])
        );

        $selectOptionCollectionRepository->getByColumn('food_composition', ColumnCode::fromString('ingredient'))
            ->willReturn(SelectOptionCollection::fromNormalized([
                ['code' => 'salt'],
                ['code' => 'SUgar'],
            ]));
        $selectOptionCollectionRepository->getByColumn('food_composition', ColumnCode::fromString('supplier'))
            ->willReturn(SelectOptionCollection::fromNormalized([
                ['code' => 'AKENEO'],
            ]));

        $this->beConstructedWith($tableConfigurationRepository, $selectOptionCollectionRepository, $getExistingRecordCodes);
    }

    function it_filters_non_existent_reference_entity_records(GetExistingRecordCodes $getExistingRecordCodes)
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::quantity() => 5,
                                            ColumnIdGenerator::record() => 'france',
                                            self::COLUMNID_RECORDBRAND => 'mars'
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::quantity() => 10,
                                            ColumnIdGenerator::record() => 'unknownRecord',
                                            self::COLUMNID_RECORDBRAND => 'france'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        );

        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(
            [
                'record' => ['france', 'unknownRecord'],
                'brand' => ['mars', 'france'],
            ]
        )->shouldBeCalledOnce()->willReturn(
            ['record' => ['FRAnce'], 'brand' => ['MArs']]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'SUgar',
                                            ColumnIdGenerator::quantity() => 5,
                                            ColumnIdGenerator::record() => 'FRAnce',
                                            self::COLUMNID_RECORDBRAND => 'MArs',
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::quantity() => 10,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_filters_rows_with_unknown_first_column_record_value(
        TableConfigurationRepository $tableConfigurationRepository,
        GetExistingRecordCodes $getExistingRecordCodes
    ) {
        $tableConfigurationRepository->getByAttributeCode('food_composition')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                ReferenceEntityColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'origin', 'reference_entity_identifier' => 'record', 'is_required_for_completeness' => true]),
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                ])
        );

        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::record() => 'france',
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::quantity() => 5
                                        ],
                                        [
                                            ColumnIdGenerator::record() => 'unknownRecord',
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::quantity() => 10
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::record() => 'otherUnknownRecord',
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::quantity() => 5
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        );

        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(['record' => ['france', 'unknownRecord']])->shouldBeCalledOnce()->willReturn(
            ['record' => ['FRAnce']]
        );

        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(['record' => ['otherUnknownRecord']])->shouldBeCalledOnce()->willReturn(
            ['record' => []]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::record() => 'FRAnce',
                                            ColumnIdGenerator::ingredient() => 'SUgar',
                                            ColumnIdGenerator::quantity() => 5
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NonExistentTableValueFilter::class);
    }

    function it_is_a_non_existent_value_filter()
    {
        $this->shouldImplement(NonExistentValuesFilter::class);
    }

    function it_filters_non_existing_columns()
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::generateAsString('removed_column') => 'foobar',
                                            ColumnIdGenerator::quantity() => 5,
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::quantity() => 10,
                                            ColumnIdGenerator::generateAsString('other_removed_column') => 'data',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                AttributeTypes::OPTION_SIMPLE_SELECT => [
                    'a_select' => [
                        [
                            'identifier' => 'product_A',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => 'option_ToTo',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'SUgar',
                                            ColumnIdGenerator::quantity() => 5,
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::quantity() => 10,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_removes_rows_which_are_empty()
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::generateAsString('removed_column') => 'foobar',
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::quantity() => 10,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::quantity() => 10,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_removes_table_values_which_are_empty()
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::generateAsString('removed_column') => 'foobar',
                                        ],
                                        [
                                            ColumnIdGenerator::generateAsString('other_removed_column') => 'salt',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldBeLike(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_filters_unknown_select_cells_and_sanitizes_the_remaining_ones()
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::supplier() => 'aKeNEo',
                                            ColumnIdGenerator::quantity() => 10,
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::supplier() => 'unknown',
                                            ColumnIdGenerator::quantity() => 20,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldReturn([
            AttributeTypes::TABLE => [
                'food_composition' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => [
                                    [
                                        ColumnIdGenerator::ingredient() => 'SUgar',
                                        ColumnIdGenerator::supplier() => 'AKENEO',
                                        ColumnIdGenerator::quantity() => 10,
                                    ],
                                    [
                                        ColumnIdGenerator::ingredient() => 'salt',
                                        ColumnIdGenerator::quantity() => 20,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    function it_filters_rows_with_unknown_first_column_value()
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::supplier() => 'akeneo',
                                            ColumnIdGenerator::quantity() => 10,
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'unknown',
                                            ColumnIdGenerator::supplier() => 'akeneo',
                                            ColumnIdGenerator::quantity() => 20,
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::supplier() => 'akeneo',
                                            ColumnIdGenerator::quantity() => 20,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'identifier' => 'product_C',
                            'values' => [
                                '<all_channels>' => [
                                    '<all_locales>' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'unknown',
                                            ColumnIdGenerator::supplier() => 'akeneo',
                                            ColumnIdGenerator::quantity() => 20,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldReturn([
            AttributeTypes::TABLE => [
                'food_composition' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => [
                                    [
                                        ColumnIdGenerator::ingredient() => 'SUgar',
                                        ColumnIdGenerator::supplier() => 'AKENEO',
                                        ColumnIdGenerator::quantity() => 10,
                                    ],
                                    [
                                        ColumnIdGenerator::ingredient() => 'salt',
                                        ColumnIdGenerator::supplier() => 'AKENEO',
                                        ColumnIdGenerator::quantity() => 20,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'identifier' => 'product_C',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    function it_filters_values_on_multiple_locales()
    {
        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType(
            [
                AttributeTypes::TABLE => [
                    'food_composition' => [
                        [
                            'identifier' => 'product_B',
                            'values' => [
                                '<all_channels>' => [
                                    'en_US' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::supplier() => 'akeneo',
                                            ColumnIdGenerator::quantity() => 10,
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'unknown',
                                            ColumnIdGenerator::supplier() => 'akeneo',
                                            ColumnIdGenerator::quantity() => 20,
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::supplier() => 'akeneo',
                                            ColumnIdGenerator::quantity() => 20,
                                        ],
                                    ],
                                    'fr_FR' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::supplier() => 'akeneo',
                                            ColumnIdGenerator::generateAsString('unknown') => 10,
                                        ],
                                    ],
                                    'de_DE' => [
                                        [
                                            ColumnIdGenerator::ingredient() => 'sugar',
                                            ColumnIdGenerator::supplier() => 'unknown',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        /** @var OnGoingFilteredRawValues $filteredCollection */
        $filteredCollection = $this->filter($ongoingFilteredRawValues);
        $filteredCollection->filteredRawValuesCollectionIndexedByType()->shouldReturn([
            AttributeTypes::TABLE => [
                'food_composition' => [
                    [
                        'identifier' => 'product_B',
                        'values' => [
                            '<all_channels>' => [
                                'en_US' => [
                                    [
                                        ColumnIdGenerator::ingredient() => 'SUgar',
                                        ColumnIdGenerator::supplier() => 'AKENEO',
                                        ColumnIdGenerator::quantity() => 10,
                                    ],
                                    [
                                        ColumnIdGenerator::ingredient() => 'salt',
                                        ColumnIdGenerator::supplier() => 'AKENEO',
                                        ColumnIdGenerator::quantity() => 20,
                                    ],
                                ],
                                'fr_FR' => [
                                    [
                                        ColumnIdGenerator::ingredient() => 'SUgar',
                                        ColumnIdGenerator::supplier() => 'AKENEO',
                                    ],
                                ],
                                'de_DE' => [
                                    [
                                        ColumnIdGenerator::ingredient() => 'SUgar',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
