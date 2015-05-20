<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\AttributeCopierInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\FieldCopierInterface;
use Prophecy\Argument;

class ProductPropertyCopierSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        CopierRegistryInterface $copierRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $copierRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductPropertyCopier');
    }

    function it_copies_a_data_to_a_product_attribute(
        $copierRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        AttributeCopierInterface $copier
    ) {
        $attributeRepository->findOneBy(['code' => 'color_one'])->willReturn($fromAttribute);
        $attributeRepository->findOneBy(['code' => 'color_two'])->willReturn($toAttribute);
        $copierRegistry->getAttributeCopier($fromAttribute, $toAttribute)->willReturn($copier);
        $copier
            ->copyAttributeData($product, $product, $fromAttribute, $toAttribute, [])
            ->shouldBeCalled();

        $this->copyData($product, $product, 'color_one', 'color_two');
    }

    function it_copies_a_data_to_a_product_field(
        $copierRegistry,
        $attributeRepository,
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        FieldCopierInterface $copier
    ) {
        $attributeRepository->findOneBy(['code' => 'category'])->willReturn(null);
        $copierRegistry->getFieldCopier('category', 'category')->willReturn($copier);
        $copier
            ->copyFieldData($fromProduct, $toProduct, 'category', 'category', [])
            ->shouldBeCalled();

        $this->copyData($fromProduct, $toProduct, 'category', 'category');
    }

    function it_throws_an_exception_when_it_copies_an_unknown_field($attributeRepository, ProductInterface $product)
    {
        $attributeRepository->findOneBy(Argument::any())->willReturn(null);
        $this->shouldThrow(new \LogicException('No copier found for fields "unknown_field" and "to_field"'))->during(
            'copyData', [$product, $product, 'unknown_field', 'to_field', []]
        );
    }
}
