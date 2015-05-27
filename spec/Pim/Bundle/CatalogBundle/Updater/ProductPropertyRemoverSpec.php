<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Remover\AttributeRemoverInterface;
use Pim\Bundle\CatalogBundle\Updater\Remover\FieldRemoverInterface;
use Pim\Bundle\CatalogBundle\Updater\Remover\RemoverRegistryInterface;
use Prophecy\Argument;

class ProductPropertyRemoverSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        RemoverRegistryInterface $removerRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $removerRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductPropertyRemover');
    }

    function it_removes_a_data_to_a_product_attribute(
        $removerRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $attribute,
        AttributeRemoverInterface $remover
    ) {
        $attributeRepository->findOneBy(['code' => 'name'])->willReturn($attribute);
        $removerRegistry->getAttributeRemover($attribute)->willReturn($remover);
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
        $attributeRepository->findOneBy(['code' => 'category'])->willReturn(null);
        $removerRegistry->getFieldRemover('category')->willReturn($remover);
        $remover
            ->removeFieldData($product, 'category', ['tshirt'], [])
            ->shouldBeCalled();

        $this->removeData($product, 'category', ['tshirt'], []);
    }

    function it_throws_an_exception_when_it_removes_an_unknown_field(
        $attributeRepository,
        $removerRegistry,
        ProductInterface $product
    ) {
        $attributeRepository->findOneBy(Argument::any())->willReturn(null);

        $removerRegistry->getFieldRemover(Argument::any())->willReturn(null);

        $this->shouldThrow(new \LogicException('No remover found for field "unknown_field"'))->during(
            'removeData', [$product, 'unknown_field', 'code']
        );
    }
}
