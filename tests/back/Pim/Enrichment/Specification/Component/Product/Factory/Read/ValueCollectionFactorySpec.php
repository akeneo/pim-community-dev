<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Read;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\BooleanValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\IdentifierValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\NumberValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\OptionValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\TextAreaValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\TextValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\ValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        ValueFactory $writeValueFactory,
        GetAttributes $getAttributeByCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {

        $valueFactory = new ReadValueFactory(
            [
                new OptionValueFactory(),
                new BooleanValueFactory(),
                new NumberValueFactory(),
                new IdentifierValueFactory(),
                new TextAreaValueFactory(),
                new TextValueFactory(),
            ],
            $writeValueFactory->getWrappedObject(),
            $attributeRepository->getWrappedObject()
        );

        $this->beConstructedWith(
            $valueFactory,
            $getAttributeByCodes,
            $chainedObsoleteValueFilter,
            new EmptyValuesCleaner()
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueCollectionFactory::class);
    }

    public function it_is_a_value_collection_factory()
    {
        $this->shouldBeAnInstanceOf(ValueCollectionFactoryInterface::class);
    }

    function it_creates_a_values_collection_from_the_storage_format_from_single(
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $sku = new Attribute('sku', AttributeTypes::IDENTIFIER, [], false, false, null);
        $description = new Attribute('description', AttributeTypes::TEXTAREA, [], true, true, null);

        $rawValues = [
            'sku' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ],
            ],
            'description' => [
                'ecommerce' => [
                    'en_US' => 'a text area for ecommerce in English',
                ],
                'tablet' => [
                    'en_US' => 'a text area for tablets in English',
                    'fr_FR' => 'une zone de texte pour les tablettes en français',

                ],
            ],
        ];

        $getAttributeByCodes->forCodes(['sku', 'description'])->willReturn([$sku, $description]);

        $valuesIndexedByType = [
            AttributeTypes::IDENTIFIER => [
                'sku' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'foo'
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ],
            AttributeTypes::TEXTAREA => [
                'description' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            'ecommerce' => [
                                'en_US' => 'a text area for ecommerce in English',
                            ],
                            'tablet' => [
                                'en_US' => 'a text area for tablets in English',
                                'fr_FR' => 'une zone de texte pour les tablettes en français',
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $ongoingNonFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($valuesIndexedByType);
        $ongoingFilteredRawValues = new OnGoingFilteredRawValues($valuesIndexedByType, []);
        $chainedObsoleteValueFilter->filterAll($ongoingNonFilteredRawValues)->willReturn($ongoingFilteredRawValues);

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldReturnAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(4);

        $actualIterator = $actualValues->getIterator();
        $actualIterator->shouldHaveKey('sku-<all_channels>-<all_locales>');
        $actualIterator->shouldHaveKey('description-ecommerce-en_US');
        $actualIterator->shouldHaveKey('description-tablet-en_US');
    }

    function it_skips_unknown_attributes_when_creating_a_values_collection_from_the_storage_format(
        GetAttributes $getAttributeByCodes
    ) {
        $rawValues = [
            'attribute_that_does_not_exists' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar'
                ]
            ]
        ];

        $getAttributeByCodes->forCodes(['attribute_that_does_not_exists'])->willReturn([]);

        $this->createFromStorageFormat($rawValues)->shouldBeLike(new ValueCollection([]));
    }

    function it_skips_unknown_attributes_when_there_are_multiple_product(
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $rawValueCollection = [
            'productA' => [
                'unknown_attribute' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'random'
                    ]
                ]
            ],
            'productB' => [
                'color' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'red'
                    ]
                ]
            ]
        ];

        $color = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null);
        $getAttributeByCodes->forCodes(['unknown_attribute', 'color'])->willReturn([$color,]);

        $typesToCode = [
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'color' => [
                    [
                        'identifier' => 'productB',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'red'
                            ]
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $onGoingNonFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($typesToCode);
        $onGoingFilteredRawValues = new OnGoingFilteredRawValues($typesToCode, []);

        $chainedObsoleteValueFilter->filterAll($onGoingNonFilteredRawValues)->willReturn($onGoingFilteredRawValues);

        $this->createMultipleFromStorageFormat($rawValueCollection)->shouldBeLike([
            'productB' => new ValueCollection([OptionValue::value('color', 'red')]),
            'productA' => new ValueCollection([]),
        ]);
    }

    function it_skips_unknown_option_when_creating_a_values_collection_from_the_storage_format(
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $rawValues = [
            'color' => [
                '<all_channels>' => [
                    '<all_locales>' => 'red'
                ],
            ],
        ];

        $getAttributeByCodes->forCodes(['color'])->willReturn([
            new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null),
        ]);

        $rawValueCollectionIndexedByType = [
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'color' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'red'
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $onGoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValueCollectionIndexedByType);

        $filteredRawValues = [
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'color' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => ''
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];
        $chainedObsoleteValueFilter->filterAll($onGoingFilteredRawValues)->willReturn(
            new OnGoingFilteredRawValues($filteredRawValues, [])
        );

        $this->createFromStorageFormat($rawValues)->shouldBeLike(new ValueCollection([]));
    }

    function it_does_not_filter_falsy_values(
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $rawValues = [
            'number' => [
                '<all_channels>' => [
                    '<all_locales>' => 0.0,
                ],
            ],
            'text' => [
                '<all_channels>' => [
                    '<all_locales>' => '0',
                ],
            ],
            'yes_no' => [
                '<all_channels>' => [
                    '<all_locales>' => false,
                ],
            ],
        ];

        $number = new Attribute('number', AttributeTypes::NUMBER, [], false, false, null);
        $text = new Attribute('text', AttributeTypes::TEXTAREA, [], false, false, null);
        $yesNo = new Attribute('yes_no', AttributeTypes::BOOLEAN, [], false, false, null);
        $getAttributeByCodes->forCodes(['number', 'text', 'yes_no'])->willReturn([$number, $text, $yesNo]);

        $typesToCode = [
            AttributeTypes::NUMBER => [
                'number' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 0.0,
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ],
            AttributeTypes::TEXTAREA => [
                'text' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => '0',
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ],
            AttributeTypes::BOOLEAN => [
                'yes_no' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => false,
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $onGoingNonFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($typesToCode);
        $onGoingFilteredRawValues = new OnGoingFilteredRawValues($typesToCode, []);
        $chainedObsoleteValueFilter->filterAll($onGoingNonFilteredRawValues)->willReturn($onGoingFilteredRawValues);

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldBeAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(3);
        $actualValues->getIterator()->shouldHaveKey('number-<all_channels>-<all_locales>');
        $actualValues->getIterator()->shouldHaveKey('text-<all_channels>-<all_locales>');
        $actualValues->getIterator()->shouldHaveKey('yes_no-<all_channels>-<all_locales>');
    }
}
