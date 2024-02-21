<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertySetter;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AttributeSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface;
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
        $this->shouldHaveType(PropertySetter::class);
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
