<?php

namespace spec\Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Exception\InvalidAttributeException;
use Pim\Component\Catalog\Exception\InvalidOptionException;
use Pim\Component\Catalog\Exception\InvalidOptionsException;
use Pim\Component\Catalog\Factory\ValueCollectionFactory;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Value\OptionsValue;
use Pim\Component\Catalog\Value\ScalarValue;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class ValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        ValueFactory $valueFactory,
        CachedObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($valueFactory, $attributeRepository, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueCollectionFactory::class);
    }

    function it_creates_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        AttributeInterface $sku,
        AttributeInterface $description,
        ValueInterface $value1,
        ValueInterface $value2,
        ValueInterface $value3,
        ValueInterface $value4
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->isUnique()->willReturn(false);
        $description->getCode()->willReturn('description');
        $description->isUnique()->willReturn(false);

        $value1->getLocale()->willReturn(null);
        $value1->getScope()->willReturn(null);
        $value1->getAttribute()->willReturn($sku);
        $value1->getData()->willReturn('1234');
        $value2->getScope()->willReturn('ecommerce');
        $value2->getLocale()->willReturn('en_US');
        $value2->getAttribute()->willReturn($description);
        $value2->getData()->willReturn('a description');
        $value3->getScope()->willReturn('tablet');
        $value3->getLocale()->willReturn('en_US');
        $value3->getAttribute()->willReturn($description);
        $value3->getData()->willReturn('a tablet description');
        $value4->getScope()->willReturn('tablet');
        $value4->getLocale()->willReturn('fr_FR');
        $value4->getAttribute()->willReturn($description);
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

        $actualValues = $this->createFromStorageFormat([
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
        ]);

        $actualValues->shouldReturnAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(4);

        $actualIterator = $actualValues->getIterator();
        $actualIterator->shouldHaveKeyWithValue('sku-<all_channels>-<all_locales>', $value1);
        $actualIterator->shouldHaveKeyWithValue('description-ecommerce-en_US', $value2);
        $actualIterator->shouldHaveKeyWithValue('description-tablet-en_US', $value3);
    }

    function it_skips_unknown_attributes_when_creating_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        $logger
    ) {
        $attributeRepository->findOneByIdentifier('attribute_that_does_not_exists')->willReturn(null);

        $valueFactory->create(Argument::cetera())->shouldNotBeCalled();
        $logger->warning('Tried to load a product value with the attribute "attribute_that_does_not_exists" that does not exist.');

        $actualValues = $this->createFromStorageFormat([
            'attribute_that_does_not_exists' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar'
                ]
            ]
        ]);

        $actualValues->shouldReturnAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_skips_unknown_option_when_creating_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        $logger,
        AttributeInterface $color
    ) {
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $valueFactory->create($color, null, null, 'red', true)->willThrow(
            InvalidOptionException::validEntityCodeExpected(
                'color',
                'code',
                'The option does not exist',
                static::class,
                'red'
            )
        );

        $logger->warning('Tried to load a product value with the option "color.red" that does not exist.');

        $actualValues = $this->createFromStorageFormat([
            'color' => [
                '<all_channels>' => [
                    '<all_locales>' => 'red'
                ],
            ],
        ]);

        $actualValues->shouldReturnAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_skips_unknown_options_when_creating_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        $logger,
        AttributeInterface $color,
        ValueInterface $purpleColor
    ) {
        $color->getCode()->willReturn('code');
        $color->isUnique()->willReturn(false);
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $valueFactory->create($color, null, null, ['red', 'purple', 'yellow'], true)->willThrow(
            InvalidOptionsException::validEntityListCodesExpected(
                'color',
                'codes',
                'The options do not exist',
                static::class,
                ['red', 'yellow']
            )
        );

        $purpleColor->getAttribute()->willReturn($color);
        $purpleColor->getLocale()->willReturn(null);
        $purpleColor->getScope()->willReturn(null);
        $purpleColor->getData()->willReturn('purple');
        $valueFactory->create($color, null, null, [1 => 'purple'])->willReturn($purpleColor);
        $logger->warning('Tried to load a product value with the options "red, yellow" that do not exist.')->shouldBeCalled();

        $actualValues = $this->createFromStorageFormat([
            'color' => [
                '<all_channels>' => [
                    '<all_locales>' => ['red', 'purple', 'yellow']
                ],
            ],
        ]);

        $actualValues->shouldReturnAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(1);
    }

    function it_skips_invalid_attributes_when_creating_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        $logger,
        AttributeInterface $color
    ) {
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $valueFactory->create($color, null, null, 'red', true)->willThrow(
            new InvalidAttributeException('attribute', 'color', static::class)
        );

        $logger->warning(Argument::containingString('Tried to load a product value with an invalid attribute "color".'));

        $actualValues = $this->createFromStorageFormat([
            'color' => [
                '<all_channels>' => [
                    '<all_locales>' => 'red'
                ],
            ],
        ]);

        $actualValues->shouldReturnAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_skips_unknown_property_when_creating_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        $logger,
        AttributeInterface $referenceData
    ) {
        $attributeRepository->findOneByIdentifier('image')->willReturn($referenceData);
        $valueFactory->create($referenceData, null, null, 'my_image', true)->willThrow(
            new InvalidPropertyException('attribute', 'image', static::class)
        );

        $logger->warning(
            Argument::containingString('Tried to load a product value with the property "image" that does not exist.')
        );

        $actualValues = $this->createFromStorageFormat([
            'image' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my_image'
                ],
            ],
        ]);

        $actualValues->shouldReturnAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_create_empty_value_is_wrong_format_when_creating_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        $logger,
        AttributeInterface $image,
        ValueInterface $value1,
        AttributeInterface $referenceData
    ) {
        $image->getCode()->willReturn('image');
        $image->isUnique()->willReturn(false);
        $value1->getLocale()->willReturn(null);
        $value1->getScope()->willReturn(null);
        $value1->getAttribute()->willReturn($image);
        $value1->getData()->willReturn('my_image');

        $attributeRepository->findOneByIdentifier('image')->willReturn($referenceData);
        $valueFactory->create($referenceData, null, null, 'my_image', true)->willThrow(
            new InvalidPropertyTypeException('attribute', 'image', static::class)
        );
        $valueFactory->create($referenceData, null, null, 'empty_image', true)->willReturn($value1);

        $logger->warning(
            Argument::containingString('Tried to load a product value for attribute "image" that does not have the good type.')
        );

        $actualValues = $this->createFromStorageFormat([
            'image' => [
                '<all_channels>' => [
                    '<all_locales>' => 'empty_image'
                ],
            ],
        ]);

        $actualValues->shouldReturnAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(1);
    }

    function it_does_not_return_null_or_empty_string_values(
        $valueFactory,
        $attributeRepository
    ) {
        $description = new Attribute();
        $description->setCode('description');
        $description->setUnique(false);
        $description->setScopable(true);
        $description->setLocalizable(true);
        $attributeRepository->findOneByIdentifier('description')->willReturn($description);

        $valueFactory
            ->create($description, 'ecommerce', 'en_US', '', true)
            ->willReturn(new ScalarValue($description, 'ecommerce', 'en_US', ''));
        $valueFactory
            ->create($description, 'ecommerce', 'fr_FR', null, true)
            ->willReturn(new ScalarValue($description, 'ecommerce', 'fr_FR', null));

        $actualValues = $this->createFromStorageFormat([
            'description' => [
                'ecommerce' => [
                    'en_US' => '',
                    'fr_FR' => null,
                ],
            ],
        ]);

        $actualValues->shouldBeAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_does_not_return_empty_array_values(
        $valueFactory,
        $attributeRepository
    ) {
        $colors = new Attribute();
        $colors->setCode('colors');
        $colors->setUnique(false);
        $colors->setScopable(false);
        $colors->setLocalizable(false);
        $attributeRepository->findOneByIdentifier('colors')->willReturn($colors);

        $valueFactory
            ->create($colors, null, null, [], true)
            ->willReturn(new OptionsValue($colors, null, null, []));

        $actualValues = $this->createFromStorageFormat(
            [
                'colors' => [
                    '<all_channels>' => [
                        '<all_locales>' => [],
                    ],
                ],
            ]
        );

        $actualValues->shouldBeAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_does_not_filter_falsy_values(
        $valueFactory,
        $attributeRepository
    ) {
        $number = new Attribute();
        $number->setCode('number');
        $number->setUnique(false);
        $number->setScopable(false);
        $number->setLocalizable(false);
        $attributeRepository->findOneByIdentifier('number')->willReturn($number);

        $text = new Attribute();
        $text->setCode('text');
        $text->setUnique(false);
        $text->setScopable(false);
        $text->setLocalizable(false);
        $attributeRepository->findOneByIdentifier('text')->willReturn($text);

        $yesNo = new Attribute();
        $yesNo->setCode('yes_no');
        $yesNo->setUnique(false);
        $yesNo->setScopable(false);
        $yesNo->setLocalizable(false);
        $attributeRepository->findOneByIdentifier('yes_no')->willReturn($yesNo);

        $valueFactory
            ->create($number, null, null, 0.0, true)
            ->willReturn(new ScalarValue($number, null, null, 0.0));
        $valueFactory
            ->create($text, null, null, '0', true)
            ->willReturn(new ScalarValue($text, null, null, '0'));
        $valueFactory
            ->create($yesNo, null, null, false, true)
            ->willReturn(new ScalarValue($yesNo, null, null, false));

        $actualValues = $this->createFromStorageFormat(
            [
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
            ]
        );

        $actualValues->shouldBeAnInstanceOf(ValueCollection::class);
        $actualValues->shouldHaveCount(3);
        $actualValues->getIterator()->shouldHaveKey('number-<all_channels>-<all_locales>');
        $actualValues->getIterator()->shouldHaveKey('text-<all_channels>-<all_locales>');
        $actualValues->getIterator()->shouldHaveKey('yes_no-<all_channels>-<all_locales>');
    }
}
