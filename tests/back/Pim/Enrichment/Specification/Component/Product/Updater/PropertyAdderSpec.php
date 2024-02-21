<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyAdder;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AttributeAdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;

class PropertyAdderSpec extends ObjectBehavior
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
        $this->shouldHaveType(PropertyAdder::class);
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

    function it_adds_a_data_to_a_product_model_attribute(
        $adderRegistry,
        $attributeRepository,
        ProductModelInterface $productModel,
        AttributeInterface $attribute,
        AttributeAdderInterface $adder
    ) {
        $attributeRepository->findOneByIdentifier('color')->willReturn($attribute);
        $adderRegistry->getAdder('color')->willReturn($adder);
        $adder
            ->addAttributeData($productModel, $attribute, ['red', 'blue'], [])
            ->shouldBeCalled();

        $this->addData($productModel, 'color', ['red', 'blue'], []);
    }

    function it_adds_a_data_to_a_product_model_field(
        $adderRegistry,
        $attributeRepository,
        ProductModelInterface $productModel,
        FieldAdderInterface $adder
    ) {
        $attributeRepository->findOneByIdentifier('category')->willReturn(null);
        $adderRegistry->getAdder('category')->willReturn($adder);
        $adder
            ->addFieldData($productModel, 'category', 'tshirt', [])
            ->shouldBeCalled();

        $this->addData($productModel, 'category', 'tshirt', []);
    }

    function it_throws_an_exception_when_trying_to_add_anything_else_than_a_product_or_a_product_model()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                EntityWithValuesInterface::class
            )
        )->during(
            'addData',
            [new \stdClass(), 'category', []]
        );
    }
}
