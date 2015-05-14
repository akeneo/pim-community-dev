<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\AdderRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\AttributeAdderInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\FieldAdderInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\AttributeCopierInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\FieldCopierInterface;
use Pim\Bundle\CatalogBundle\Updater\Remover\AttributeRemoverInterface;
use Pim\Bundle\CatalogBundle\Updater\Remover\FieldRemoverInterface;
use Pim\Bundle\CatalogBundle\Updater\Remover\RemoverRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\AttributeSetterInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\FieldSetterInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\SetterRegistryInterface;
use Prophecy\Argument;

class ProductFieldUpdaterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        SetterRegistryInterface $setterRegistry,
        CopierRegistryInterface $copierRegistry,
        AdderRegistryInterface $adderRegistry,
        RemoverRegistryInterface $removerRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $setterRegistry,
            $copierRegistry,
            $adderRegistry,
            $removerRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductFieldUpdater');
    }

    function it_sets_a_data_to_a_product_attribute(
        $setterRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $attribute,
        AttributeSetterInterface $setter
    ) {
        $attributeRepository->findOneBy(['code' => 'name'])->willReturn($attribute);
        $setterRegistry->getAttributeSetter($attribute)->willReturn($setter);
        $setter
            ->setAttributeData($product, $attribute, 'my name', [])
            ->shouldBeCalled();

        $this->setData($product, 'name', 'my name', []);
    }

    function it_sets_a_data_to_a_product_field(
        $setterRegistry,
        $attributeRepository,
        ProductInterface $product,
        FieldSetterInterface $setter
    ) {
        $attributeRepository->findOneBy(['code' => 'category'])->willReturn(null);
        $setterRegistry->getFieldSetter('category')->willReturn($setter);
        $setter
            ->setFieldData($product, 'category', ['tshirt'], [])
            ->shouldBeCalled();

        $this->setData($product, 'category', ['tshirt'], []);
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

    function it_throws_an_exception_when_it_sets_an_unknown_field($attributeRepository, ProductInterface $product)
    {
        $attributeRepository->findOneBy(Argument::any())->willReturn(null);
        $this->shouldThrow(new \LogicException('No setter found for field "unknown_field"'))->during(
            'setData', [$product, 'unknown_field', 'data', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]
        );
    }

    function it_throws_an_exception_when_it_copies_an_unknown_field($attributeRepository, ProductInterface $product)
    {
        $attributeRepository->findOneBy(Argument::any())->willReturn(null);
        $this->shouldThrow(new \LogicException('No copier found for fields "unknown_field" and "to_field"'))->during(
            'copyData', [$product, $product, 'unknown_field', 'to_field', []]
        );
    }
}
