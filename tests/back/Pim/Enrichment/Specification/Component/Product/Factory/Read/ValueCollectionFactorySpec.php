<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Read;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\BooleanValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\IdentifierValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ImageValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\NumberValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\OptionValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\TextAreaValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\TextValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\ValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class ValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        ValueFactory $writeValueFactory,
        GetAttributes $getAttributeByCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter,
        LoggerInterface $logger,
        FileInfoRepositoryInterface $fileInfoRepository
    ) {

        $valueFactory = new ReadValueFactory(
            [
                new OptionValueFactory(),
                new BooleanValueFactory(),
                new NumberValueFactory(),
                new IdentifierValueFactory(),
                new TextAreaValueFactory(),
                new TextValueFactory(),
                new ImageValueFactory($fileInfoRepository->getWrappedObject()),
            ],
            $writeValueFactory->getWrappedObject(),
            $attributeRepository->getWrappedObject()
        );

        $this->beConstructedWith(
            $valueFactory,
            $getAttributeByCodes,
            $chainedObsoleteValueFilter,
            new EmptyValuesCleaner(),
            $logger
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueCollectionFactory::class);
    }

    function it_creates_a_values_collection_from_the_storage_format_from_single(
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $sku = new Attribute('sku', AttributeTypes::IDENTIFIER, [], false, false, null, false);
        $description = new Attribute('description', AttributeTypes::TEXTAREA, [], true, true, null, false);

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

        $getAttributeByCodes->forCodes(['sku', 'description'])->willReturn(['sku' => $sku, 'description' => $description]);

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

        $actualValues->shouldReturnAnInstanceOf(ReadValueCollection::class);
        $actualValues->shouldBeLike(new ReadValueCollection(
            [
                ScalarValue::value('sku', 'foo'),
                ScalarValue::scopableLocalizableValue('description', 'a text area for ecommerce in English', 'ecommerce', 'en_US'),
                ScalarValue::scopableLocalizableValue('description', 'a text area for tablets in English', 'tablet', 'en_US'),
                ScalarValue::scopableLocalizableValue('description', 'une zone de texte pour les tablettes en français', 'tablet', 'fr_FR'),
            ]
        ));
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

        $getAttributeByCodes->forCodes(['attribute_that_does_not_exists'])->willReturn(['attribute_that_does_not_exists' => null]);

        $this->createFromStorageFormat($rawValues)->shouldBeLike(new ReadValueCollection([]));
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

        $color = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false);
        $getAttributeByCodes->forCodes(['unknown_attribute', 'color'])->willReturn(['color' => $color, 'unknown_attribute' => null]);

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
            'productB' => new ReadValueCollection([OptionValue::value('color', 'red')]),
            'productA' => new ReadValueCollection([]),
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
            'color' => new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false)
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

        $this->createFromStorageFormat($rawValues)->shouldBeLike(new ReadValueCollection([]));
    }

    function it_skips_invalid_property_when_creating_a_values_collection_from_the_storage_format(
        GetAttributes $getAttributeByCodes,
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter,
        LoggerInterface $logger,
        AttributeInterface $referenceData
    ) {
        $rawValues = [
            'image' => [
                '<all_channels>' => [
                    '<all_locales>' => 'empty_image'
                ],
            ],
        ];

        $getAttributeByCodes->forCodes(['image'])->willReturn([
            'image' => new Attribute('image', AttributeTypes::IMAGE, [], false, false, null, false),
        ]);

        $rawValueCollectionIndexedByType = [
            AttributeTypes::IMAGE => [
                'image' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'empty_image'
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $onGoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValueCollectionIndexedByType);

        $chainedObsoleteValueFilter->filterAll($onGoingFilteredRawValues)->willReturn(
            new OnGoingFilteredRawValues($rawValueCollectionIndexedByType, [])
        );

        $attributeRepository->findOneByIdentifier('image')->willReturn($referenceData);
        $valueFactory->create($referenceData, null, null, 'empty_image', true)->willThrow(
            new InvalidPropertyException('attribute', 'image', static::class)
        );

        $logger->notice(
            Argument::containingString('Tried to load a product value with the property "empty_image" that does not exist.')
        )->shouldBeCalled();

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldReturnAnInstanceOf(ReadValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_skips_invalid_property_type_when_creating_a_values_collection_from_the_storage_format(
        GetAttributes $getAttributeByCodes,
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter,
        LoggerInterface $logger,
        AttributeInterface $referenceData
    ) {
        $rawValues = [
            'image' => [
                '<all_channels>' => [
                    '<all_locales>' => 'empty_image'
                ],
            ],
        ];

        $getAttributeByCodes->forCodes(['image'])->willReturn([
            'image' => new Attribute('image', AttributeTypes::IMAGE, [], false, false, null, false),
        ]);

        $rawValueCollectionIndexedByType = [
            AttributeTypes::IMAGE => [
                'image' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'empty_image'
                            ],
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $onGoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValueCollectionIndexedByType);

        $chainedObsoleteValueFilter->filterAll($onGoingFilteredRawValues)->willReturn(
            new OnGoingFilteredRawValues($rawValueCollectionIndexedByType, [])
        );

        $attributeRepository->findOneByIdentifier('image')->willReturn($referenceData);
        $valueFactory->create($referenceData, null, null, 'empty_image', true)->willThrow(
            new InvalidPropertyTypeException('attribute', 'image', static::class)
        );

        $logger->notice(
            Argument::containingString('Tried to load a product value with the property "empty_image" that does not exist.')
        )->shouldBeCalled();

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldReturnAnInstanceOf(ReadValueCollection::class);
        $actualValues->shouldHaveCount(0);
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

        $number = new Attribute('number', AttributeTypes::NUMBER, [], false, false, null, false);
        $text = new Attribute('text', AttributeTypes::TEXTAREA, [], false, false, null, false);
        $yesNo = new Attribute('yes_no', AttributeTypes::BOOLEAN, [], false, false, null, false);
        $getAttributeByCodes->forCodes(['number', 'text', 'yes_no'])->willReturn(['number' => $number, 'text' => $text, 'yes_no' => $yesNo]);

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

        $actualValues->shouldBeAnInstanceOf(ReadValueCollection::class);
        $actualValues->shouldHaveCount(3);
        $actualValues->shouldBeLike(new ReadValueCollection([
            ScalarValue::value('number', 0.0),
            ScalarValue::value('text', '0'),
            ScalarValue::value('yes_no', false),
        ]));
    }
}
