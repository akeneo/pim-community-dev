<?php

namespace spec\Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValueCollectionFactory;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueInterface;

class ProductValueCollectionFactorySpec extends ObjectBehavior
{
    function let(ProductValueFactory $valueFactory, CachedObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($valueFactory, $attributeRepository);
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
        $sku->getCode()->wilLReturn('sku');
        $sku->isUnique()->wilLReturn(false);
        $description->getCode()->wilLReturn('description');
        $description->isUnique()->wilLReturn(false);

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
        $attributeRepository->findOneByIdentifier('attribute_that_does_not_exists')->willReturn(null);

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
            'attribute_that_does_not_exists' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar'
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
}
