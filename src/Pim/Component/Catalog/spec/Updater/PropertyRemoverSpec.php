<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Updater\Remover\AttributeRemoverInterface;
use Pim\Component\Catalog\Updater\Remover\FieldRemoverInterface;
use Pim\Component\Catalog\Updater\Remover\RemoverRegistryInterface;
use Prophecy\Argument;

class PropertyRemoverSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        RemoverRegistryInterface $removerRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $removerRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\PropertyRemover');
    }

    function it_removes_a_data_to_a_product_attribute(
        $removerRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $attribute,
        AttributeRemoverInterface $remover
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $removerRegistry->getRemover('name')->willReturn($remover);
        $remover
            ->removeAttributeData($product, $attribute, 'my name', [])
            ->shouldBeCalled();

        $this->removeData($product, 'name', 'my name', []);
    }

    function it_removes_a_data_to_a_product_field(
        $removerRegistry,
        $attributeRepository,
        ProductInterface $product,
        FieldRemoverInterface $remover
    ) {
        $attributeRepository->findOneByIdentifier('category')->willReturn(null);
        $removerRegistry->getRemover('category')->willReturn($remover);
        $remover
            ->removeFieldData($product, 'category', ['tshirt'], [])
            ->shouldBeCalled();

        $this->removeData($product, 'category', ['tshirt'], []);
    }

    function it_removes_a_data_to_a_product_model_attribute(
        $removerRegistry,
        $attributeRepository,
        ProductModelInterface $productModel,
        AttributeInterface $attribute,
        AttributeRemoverInterface $remover
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $removerRegistry->getRemover('name')->willReturn($remover);
        $remover
            ->removeAttributeData($productModel, $attribute, 'my name', [])
            ->shouldBeCalled();

        $this->removeData($productModel, 'name', 'my name', []);
    }

    function it_removes_a_data_to_a_product_model_field(
        $removerRegistry,
        $attributeRepository,
        ProductModelInterface $productModel,
        FieldRemoverInterface $remover
    ) {
        $attributeRepository->findOneByIdentifier('category')->willReturn(null);
        $removerRegistry->getRemover('category')->willReturn($remover);
        $remover
            ->removeFieldData($productModel, 'category', ['tshirt'], [])
            ->shouldBeCalled();

        $this->removeData($productModel, 'category', ['tshirt'], []);
    }

    function it_throws_an_exception_when_it_removes_an_unknown_field(
        $attributeRepository,
        $removerRegistry,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn(null);

        $removerRegistry->getRemover(Argument::any())->willReturn(null);

        $this->shouldThrow(new \LogicException('No remover found for field "unknown_field"'))->during(
            'removeData', [$product, 'unknown_field', 'code']
        );
    }

    function it_throws_an_exception_when_trying_to_remove_anything_else_than_a_product_or_a_product_model()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\EntityWithValuesInterface'
            )
        )->during(
            'removeData',
            [new \stdClass(), 'category', []]
        );
    }
}
