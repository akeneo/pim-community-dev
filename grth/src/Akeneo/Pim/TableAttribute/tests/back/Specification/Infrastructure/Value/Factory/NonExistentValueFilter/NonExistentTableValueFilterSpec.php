<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory\NonExistentValueFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\RecordColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory\NonExistentValueFilter\NonExistentTableValueFilter;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class NonExistentTableValueFilterSpec extends ObjectBehavior
{
    const COLUMNID_RECORDB = 'recordB_d39d3c48-46e6-4744-8196-56e08563fd46';

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
                RecordColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'recordA', 'reference_entity_identifier' => 'reference_entityA']),
                RecordColumn::fromNormalized(['id' => self::COLUMNID_RECORDB, 'code' => 'recordB', 'reference_entity_identifier' => 'reference_entityB']),
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
                                            ColumnIdGenerator::record() => 'recordA',
                                            self::COLUMNID_RECORDB => 'recordB'
                                        ],
                                        [
                                            ColumnIdGenerator::ingredient() => 'salt',
                                            ColumnIdGenerator::quantity() => 10,
                                            ColumnIdGenerator::record() => 'unknownRecordA',
                                            self::COLUMNID_RECORDB => 'unknownRecordB'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        );

        $referenceEntityA = ReferenceEntityIdentifier::fromString('reference_entityA');
        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes($referenceEntityA, ['recordA', 'unknownRecordA'])->shouldBeCalledOnce()->willReturn(
            ['RECordA']
        );

        $referenceEntityB = ReferenceEntityIdentifier::fromString('reference_entityB');
        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes($referenceEntityB, ['recordB', 'unknownRecordB'])->shouldBeCalledOnce()->willReturn(
            ['RECOrdB']
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
                                            ColumnIdGenerator::record() => 'RECordA',
                                            self::COLUMNID_RECORDB => 'RECOrdB',
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
                RecordColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'recordA', 'reference_entity_identifier' => 'reference_entityA', 'is_required_for_completeness' => true]),
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
                                            ColumnIdGenerator::record() => 'recordA',
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
                                            ColumnIdGenerator::record() => 'unknownRecord',
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

        $referenceEntityA = ReferenceEntityIdentifier::fromString('reference_entityA');
        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes($referenceEntityA, ['recordA', 'unknownRecord'])->shouldBeCalledOnce()->willReturn(
            ['RECordA']
        );

        $referenceEntityA = ReferenceEntityIdentifier::fromString('reference_entityA');
        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes($referenceEntityA, ['unknownRecord'])->shouldBeCalledOnce()->willReturn(
            []
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
                                            ColumnIdGenerator::record() => 'RECordA',
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
