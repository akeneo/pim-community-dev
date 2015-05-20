<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\AdderRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\AttributeAdderInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\FieldAdderInterface;

class ProductPropertyAdderSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        AdderRegistryInterface $adderRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $adderRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductPropertyAdder');
    }

    function it_adds_a_data_to_a_product_attribute(
        $adderRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $attribute,
        AttributeAdderInterface $adder
    ) {
        $attributeRepository->findOneBy(['code' => 'color'])->willReturn($attribute);
        $adderRegistry->getAttributeAdder($attribute)->willReturn($adder);
        $adder
            ->addAttributeData($product, $attribute, ['red', 'blue'], [])
            ->shouldBeCalled();

        $this->addData($product, 'color', ['red', 'blue'], []);
    }

    function it_adds_a_data_to_a_product_field(
        $adderRegistry,
        $attributeRepository,
        ProductInterface $product,
        FieldAdderInterface $adder
    ) {
        $attributeRepository->findOneBy(['code' => 'category'])->willReturn(null);
        $adderRegistry->getFieldAdder('category')->willReturn($adder);
        $adder
            ->addFieldData($product, 'category', 'tshirt', [])
            ->shouldBeCalled();

        $this->addData($product, 'category', 'tshirt', []);
    }
}
