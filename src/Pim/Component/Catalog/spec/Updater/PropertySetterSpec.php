<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use Pim\Component\Catalog\Updater\Setter\SetterRegistryInterface;
use Prophecy\Argument;

class PropertySetterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        SetterRegistryInterface $setterRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $setterRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\PropertySetter');
    }

    function it_sets_a_data_to_a_product_attribute(
        $setterRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $attribute,
        AttributeSetterInterface $setter
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $setterRegistry->getSetter('name')->willReturn($setter);
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
        $attributeRepository->findOneByIdentifier('category')->willReturn(null);
        $setterRegistry->getSetter('category')->willReturn($setter);
        $setter
            ->setFieldData($product, 'category', ['tshirt'], [])
            ->shouldBeCalled();

        $this->setData($product, 'category', ['tshirt'], []);
    }

    function it_throws_an_exception_when_it_sets_an_unknown_field($attributeRepository, ProductInterface $product)
    {
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn(null);
        $this->shouldThrow(
            UnknownPropertyException::unknownProperty('unknown_field')
        )->during(
            'setData', [$product, 'unknown_field', 'data', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]
        );
    }
}
