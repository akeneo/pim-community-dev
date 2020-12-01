<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\Attribute as StructureAttribute;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class WriteValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $this->beConstructedWith(
            $valueFactory,
            $attributeRepository,
            $logger,
            $getAttributeByCodes,
            $chainedObsoleteValueFilter,
            new EmptyValuesCleaner()
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WriteValueCollectionFactory::class);
    }

    function it_creates_a_values_collection_from_the_storage_format_from_single(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeInterface $sku,
        AttributeInterface $description,
        ValueInterface $value1,
        ValueInterface $value2,
        ValueInterface $value3,
        ValueInterface $value4,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
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

        $getAttributeByCodes->forCodes(['sku', 'description'])->willReturn([
            'sku' => new Attribute('sku', AttributeTypes::IDENTIFIER, [], false, false, null, false),
            'description' => new Attribute('description', AttributeTypes::TEXTAREA, [], false, false, null, false)
        ]);

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

        $sku->getCode()->willReturn('sku');
        $sku->isUnique()->willReturn(false);
        $description->getCode()->willReturn('description');
        $description->isUnique()->willReturn(false);

        $value1->getLocaleCode()->willReturn(null);
        $value1->getScopeCode()->willReturn(null);
        $value1->getAttributeCode()->willReturn('sku');
        $value1->getData()->willReturn('1234');
        $value2->getScopeCode()->willReturn('ecommerce');
        $value2->getLocaleCode()->willReturn('en_US');
        $value2->getAttributeCode()->willReturn('description');
        $value2->getData()->willReturn('a description');
        $value3->getScopeCode()->willReturn('tablet');
        $value3->getLocaleCode()->willReturn('en_US');
        $value3->getAttributeCode()->willReturn('description');
        $value3->getData()->willReturn('a tablet description');
        $value4->getScopeCode()->willReturn('tablet');
        $value4->getLocaleCode()->willReturn('fr_FR');
        $value4->getAttributeCode()->willReturn('description');
        $value4->getData()->willReturn('une description');

        $attributeRepository->findOneByIdentifier('sku')->willReturn($sku);
        $attributeRepository->findOneByIdentifier('description')->willReturn($description);

        $valueFactory->create($sku, null, null, 'foo', true)->willReturn($value1);
        $valueFactory
            ->create($description, 'ecommerce', 'en_US', 'a text area for ecommerce in English', true)
            ->willReturn($value2);
        $valueFactory
            ->create($description, 'tablet', 'en_US', 'a text area for tablets in English', true)
            ->willReturn($value3);
        $valueFactory
            ->create($description, 'tablet', 'fr_FR', 'une zone de texte pour les tablettes en français', true)
            ->willReturn($value4);

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldReturnAnInstanceOf(WriteValueCollection::class);
        $actualValues->shouldHaveCount(4);

        $actualIterator = $actualValues->getIterator();
        $actualIterator->shouldHaveKeyWithValue('sku-<all_channels>-<all_locales>', $value1);
        $actualIterator->shouldHaveKeyWithValue('description-ecommerce-en_US', $value2);
        $actualIterator->shouldHaveKeyWithValue('description-tablet-en_US', $value3);
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

        $this->createFromStorageFormat($rawValues)->shouldBeLike(new WriteValueCollection([]));
    }

    function it_skips_unknown_attributes_when_there_are_multiple_product(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeInterface $color,
        ValueInterface $value,
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

        $getAttributeByCodes->forCodes(['unknown_attribute', 'color'])->willReturn([
            'color' => new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false),
        ]);

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

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $valueFactory->create($color, null, null, 'red', true)->willReturn($value);
        $value->getAttributeCode()->willReturn('color');
        $value->getScopeCode()->willReturn('<all_channels>');
        $value->getLocaleCode()->willReturn('<all_locales>');
        $value->getData()->willReturn('red');
        $chainedObsoleteValueFilter->filterAll($onGoingNonFilteredRawValues)->willReturn($onGoingFilteredRawValues);

        $this->createMultipleFromStorageFormat($rawValueCollection)->shouldBeLike([
            'productB' => new WriteValueCollection([$value->getWrappedObject()]),
            'productA' => new WriteValueCollection([]),
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
            'color' => new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false),
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

        $this->createFromStorageFormat($rawValues)->shouldBeLike(new WriteValueCollection([]));
    }

    function it_skips_invalid_attributes_when_creating_a_values_collection_from_the_storage_format_single(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        AttributeInterface $color,
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
           'color' => new Attribute('color', AttributeTypes::OPTION_MULTI_SELECT, [], false, false, null, false),
        ]);

        $typesToCode = [
            AttributeTypes::OPTION_MULTI_SELECT => [
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

        $onGoingNonFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($typesToCode);
        $onGoingFilteredRawValues = new OnGoingFilteredRawValues($typesToCode, []);
        $chainedObsoleteValueFilter->filterAll($onGoingNonFilteredRawValues)->willReturn($onGoingFilteredRawValues);

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $valueFactory->create($color, null, null, 'red', true)->willThrow(
            new InvalidAttributeException('attribute', 'color', static::class)
        );

        $logger->notice(Argument::containingString('Tried to load a product value with an invalid attribute "color".'));

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldReturnAnInstanceOf(WriteValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_skips_unknown_property_when_creating_a_values_collection_from_the_storage_format(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        AttributeInterface $referenceData,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $rawValues = [
            'image' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my_image'
                ],
            ],
        ];

        $getAttributeByCodes->forCodes(['image'])->willReturn([
            'image' => new Attribute('image', AttributeTypes::IMAGE, [], false, false, null, false),
        ]);

        $typesToCode = [
            AttributeTypes::IMAGE => [
                'image' => [
                    [
                        'identifier' => 'not_used_identifier',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'my_image'
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

        $attributeRepository->findOneByIdentifier('image')->willReturn($referenceData);
        $valueFactory->create($referenceData, null, null, 'my_image', true)->willThrow(
            new InvalidPropertyException('attribute', 'image', static::class)
        );

        $logger->notice(
            Argument::containingString('Tried to load a product value with the property "image" that does not exist.')
        )->shouldBeCalled();

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldReturnAnInstanceOf(WriteValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_skips_value_if_wrong_format_when_creating_a_values_collection_from_the_storage_format(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        AttributeInterface $image,
        ValueInterface $value1,
        AttributeInterface $referenceData,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
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

        $typesToCode = [
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

        $onGoingNonFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($typesToCode);
        $onGoingFilteredRawValues = new OnGoingFilteredRawValues($typesToCode, []);
        $chainedObsoleteValueFilter->filterAll($onGoingNonFilteredRawValues)->willReturn($onGoingFilteredRawValues);

        $image->getCode()->willReturn('image');
        $image->isUnique()->willReturn(false);
        $value1->getLocaleCode()->willReturn(null);
        $value1->getScopeCode()->willReturn(null);
        $value1->getAttributeCode()->willReturn('image');
        $value1->getData()->willReturn('empty_image');
        $referenceData->getCode()->willReturn('reference');

        $attributeRepository->findOneByIdentifier('image')->willReturn($referenceData);
        $valueFactory->create($referenceData, null, null, 'empty_image', true)->willThrow(
            new InvalidPropertyTypeException('attribute', 'image', static::class)
        );

        $logger->notice(
            Argument::containingString('Tried to load a product value for attribute "reference" that does not have the expected type in database.')
        )->shouldBeCalled();

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldReturnAnInstanceOf(WriteValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_does_not_filter_falsy_values(
        $valueFactory,
        $attributeRepository,
        AttributeInterface $number,
        AttributeInterface $text,
        AttributeInterface $yesNo,
        ValueInterface $numberValue,
        ValueInterface $textValue,
        ValueInterface $yesnoValue,
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

        $getAttributeByCodes->forCodes(['number', 'text', 'yes_no'])->willReturn([
            'number' => new Attribute('number', AttributeTypes::NUMBER, [], false, false, null, false),
            'text' => new Attribute('text', AttributeTypes::TEXTAREA, [], false, false, null, false),
            'yes_no' => new Attribute('yes_no', AttributeTypes::BOOLEAN, [], false, false, null, false),
        ]);

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

        $number->getCode()->willReturn('number');
        $number->isUnique()->willReturn(false);
        $number->isScopable()->willReturn(false);
        $number->isLocalizable()->willReturn(false);
        $attributeRepository->findOneByIdentifier('number')->willReturn($number);

        $text->getCode()->willReturn('text');
        $text->isUnique()->willReturn(false);
        $text->isScopable()->willReturn(false);
        $text->isLocalizable()->willReturn(false);
        $attributeRepository->findOneByIdentifier('text')->willReturn($text);

        $yesNo->getCode()->willReturn('yes_no');
        $yesNo->isUnique()->willReturn(false);
        $yesNo->isScopable()->willReturn(false);
        $yesNo->isLocalizable()->willReturn(false);
        $attributeRepository->findOneByIdentifier('yes_no')->willReturn($yesNo);

        $numberValue->getData()->willReturn(0.0);
        $numberValue->getAttributeCode()->willReturn('number');
        $numberValue->getScopeCode()->willReturn(null);
        $numberValue->getLocaleCode()->willReturn(null);
        $valueFactory
            ->create($number, null, null, 0.0, true)
            ->willReturn($numberValue);

        $textValue->getData()->willReturn('0');
        $textValue->getAttributeCode()->willReturn('text');
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getLocaleCode()->willReturn(null);
        $valueFactory
            ->create($text, null, null, '0', true)
            ->willReturn($textValue);

        $yesnoValue->getData()->willReturn(false);
        $yesnoValue->getAttributeCode()->willReturn('yes_no');
        $yesnoValue->getScopeCode()->willReturn(null);
        $yesnoValue->getLocaleCode()->willReturn(null);
        $valueFactory
            ->create($yesNo, null, null, false, true)
            ->willReturn($yesnoValue);

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldBeAnInstanceOf(WriteValueCollection::class);
        $actualValues->shouldHaveCount(3);
        $actualValues->getIterator()->shouldHaveKey('number-<all_channels>-<all_locales>');
        $actualValues->getIterator()->shouldHaveKey('text-<all_channels>-<all_locales>');
        $actualValues->getIterator()->shouldHaveKey('yes_no-<all_channels>-<all_locales>');
    }

    function it_does_not_ask_for_the_attribute_codes_multiple_time_for_performance(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    )
    {
        $numberAttribute = new StructureAttribute();
        $numberAttribute->setCode('number');
        $number2Attribute = new StructureAttribute();
        $number2Attribute->setCode('number2');
        $number3Attribute = new StructureAttribute();
        $number3Attribute->setCode('number3');
        $numberProductA = ScalarValue::value('number', 5);
        $numberProductB = ScalarValue::value('number', 7);
        $number2ProductA = ScalarValue::value('number2', 6);
        $number3ProductB = ScalarValue::value('number3', 8);

        $rawValues = [
            'productA' => [
                'number' => [
                    '<all_channels>' => [
                        '<all_locales>' => 5
                    ]
                ],
                'number2' => [
                    '<all_channels>' => [
                        '<all_locales>' => 6
                    ]
                ]
            ],
            'productB' => [
                'number' => [
                    '<all_channels>' => [
                        '<all_locales>' => 7
                    ]
                ],
                'number3' => [
                    '<all_channels>' => [
                        '<all_locales>' => 8
                    ]
                ],
            ]
        ];

        $getAttributeByCodes->forCodes(['number', 'number2', 'number3'])->willReturn(
            [
                'number' => new Attribute('number', AttributeTypes::NUMBER, [], false, false, null, false),
                'number2' => new Attribute('number2', AttributeTypes::NUMBER, [], false, false, null, false),
                'number3' => new Attribute('number3', AttributeTypes::NUMBER, [], false, false, null, false),
            ]
        );

        $typesToCode = [
            AttributeTypes::NUMBER => [
                'number' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 5
                            ]
                        ],
                        'properties' => [],
                    ],
                    [
                        'identifier' => 'productB',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 7
                            ]
                        ],
                        'properties' => [],
                    ]
                ],
                'number2' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 6
                            ]
                        ],
                        'properties' => [],
                    ]
                ],
                'number3' => [
                    [
                        'identifier' => 'productB',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 8
                            ]
                        ],
                        'properties' => [],
                    ]
                ]
            ]
        ];

        $ongoingNonFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($typesToCode);
        $ongoingFilteredRawValues =new OnGoingFilteredRawValues($typesToCode, []);
        $chainedObsoleteValueFilter->filterAll($ongoingNonFilteredRawValues)->willReturn($ongoingFilteredRawValues);

        $attributeRepository->findOneByIdentifier('number')->willReturn($numberAttribute);
        $attributeRepository->findOneByIdentifier('number2')->willReturn($number2Attribute);
        $attributeRepository->findOneByIdentifier('number3')->willReturn($number3Attribute);

        $valueFactory
            ->create($numberAttribute, null, null, 5, true)
            ->willReturn($numberProductA);

        $valueFactory
            ->create($numberAttribute, null, null, 7, true)
            ->willReturn($numberProductB);

        $valueFactory
            ->create($number2Attribute, null, null, 6, true)
            ->willReturn($number2ProductA);

        $valueFactory
            ->create($number3Attribute, null, null, 8, true)
            ->willReturn($number3ProductB);

        $valueCollections = $this->createMultipleFromStorageFormat($rawValues);
        $valueCollections->shouldHaveCount(2);
    }
}
