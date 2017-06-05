<?php

namespace spec\Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidAttributeException;
use Pim\Component\Catalog\Exception\InvalidOptionException;
use Pim\Component\Catalog\Factory\ProductValueCollectionFactory;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class ProductValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        ProductValueFactory $valueFactory,
        CachedObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($valueFactory, $attributeRepository, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueCollectionFactory::class);
    }

    function it_creates_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        AttributeInterface $sku,
        AttributeInterface $description,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        ProductValueInterface $value3,
        ProductValueInterface $value4
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->isUnique()->willReturn(false);
        $description->getCode()->willReturn('description');
        $description->isUnique()->willReturn(false);

        $value1->getLocale()->willReturn(null);
        $value1->getScope()->willReturn(null);
        $value1->getAttribute()->willReturn($sku);
        $value2->getScope()->willReturn('ecommerce');
        $value2->getLocale()->willReturn('en_US');
        $value2->getAttribute()->willReturn($description);
        $value3->getScope()->willReturn('tablet');
        $value3->getLocale()->willReturn('en_US');
        $value3->getAttribute()->willReturn($description);
        $value4->getScope()->willReturn('tablet');
        $value4->getLocale()->willReturn('fr_FR');
        $value4->getAttribute()->willReturn($description);

        $attributeRepository->findOneByIdentifier('sku')->willReturn($sku);
        $attributeRepository->findOneByIdentifier('description')->willReturn($description);

        $valueFactory->create($sku, null, null, 'foo')->willReturn($value1);
        $valueFactory
            ->create($description, 'ecommerce', 'en_US', 'a text area for ecommerce in English')
            ->willReturn($value2);
        $valueFactory
            ->create($description, 'tablet', 'en_US', 'a text area for tablets in English')
            ->willReturn($value3);
        $valueFactory
            ->create($description, 'tablet', 'fr_FR', 'une zone de texte pour les tablettes en français')
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

        $actualValues->shouldReturnAnInstanceOf(ProductValueCollection::class);
        $actualValues->shouldHaveCount(4);

        $actualIterator = $actualValues->getIterator();
        $actualIterator->shouldHaveKeyWithValue('sku-<all_channels>-<all_locales>', $value1);
        $actualIterator->shouldHaveKeyWithValue('description-ecommerce-en_US', $value2);
        $actualIterator->shouldHaveKeyWithValue('description-tablet-en_US', $value3);
        $actualIterator->shouldHaveKeyWithValue('description-tablet-fr_FR', $value4);
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

        $actualValues->shouldReturnAnInstanceOf(ProductValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_skips_unknown_options_when_creating_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        $logger,
        AttributeInterface $color
    ) {
        $color->getCode()->willReturn('color');
        $color->isUnique()->willReturn(false);

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $valueFactory->create($color, null, null, 'red')->willThrow(
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

        $actualValues->shouldReturnAnInstanceOf(ProductValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }

    function it_skips_invalid_attributes_when_creating_a_values_collection_from_the_storage_format(
        $valueFactory,
        $attributeRepository,
        $logger,
        AttributeInterface $color
    ) {
        $color->getCode()->willReturn('color');
        $color->isUnique()->willReturn(false);
        $color->isLocalizable()->willReturn(true);

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $valueFactory->create($color, null, null, 'red')->willThrow(
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

        $actualValues->shouldReturnAnInstanceOf(ProductValueCollection::class);
        $actualValues->shouldHaveCount(0);
    }
}
