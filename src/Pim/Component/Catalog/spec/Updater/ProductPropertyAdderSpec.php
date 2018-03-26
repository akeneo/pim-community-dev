<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Adder\AdderRegistryInterface;
use Pim\Component\Catalog\Updater\Adder\AttributeAdderInterface;
use Pim\Component\Catalog\Updater\Adder\FieldAdderInterface;

class ProductPropertyAdderSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AdderRegistryInterface $adderRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $adderRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\ProductPropertyAdder');
    }

    function it_adds_a_data_to_a_product_attribute(
        $adderRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $attribute,
        AttributeAdderInterface $adder
    ) {
        $attributeRepository->findOneByIdentifier('color')->willReturn($attribute);
        $adderRegistry->getAdder('color')->willReturn($adder);
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
        $attributeRepository->findOneByIdentifier('category')->willReturn(null);
        $adderRegistry->getAdder('category')->willReturn($adder);
        $adder
            ->addFieldData($product, 'category', 'tshirt', [])
            ->shouldBeCalled();

        $this->addData($product, 'category', 'tshirt', []);
    }

    function it_throws_an_exception_when_trying_to_add_anything_else_than_a_product_or_a_product_model()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\ProductInterface or Pim\Component\Catalog\Model\ProductModelInterface'
            )
        )->during(
            'addData',
            [new \stdClass(), 'category', []]
        );
    }
}
