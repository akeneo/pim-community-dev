<?php

namespace spec\Pim\Component\Catalog\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface;
use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryRegistry;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class ProductValueFactorySpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper, ProductValueFactoryRegistry $registry)
    {
        $this->beConstructedWith($attributeValidatorHelper, $registry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueFactory::class);
    }

    function it_creates_a_simple_empty_product_value(
        $attributeValidatorHelper,
        $registry,
        AttributeInterface $attribute,
        ProductValueFactoryInterface $productValueFactory,
        ProductValueInterface $productValue
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->getAttributeType()->willReturn('text');

        $attributeValidatorHelper->validateLocale($attribute, null)->shouldBeCalled();
        $attributeValidatorHelper->validateScope($attribute, null)->shouldBeCalled();

        $registry->get('text')->willReturn($productValueFactory);
        $productValueFactory->create($attribute, null, null)->willReturn($productValue);

        $this->create($attribute, null, null)->shouldReturn($productValue);
    }

    function it_creates_a_simple_localizable_and_scopable_empty_product_value(
        $attributeValidatorHelper,
        $registry,
        AttributeInterface $attribute,
        ProductValueFactoryInterface $productValueFactory,
        ProductValueInterface $productValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->getAttributeType()->willReturn('text');

        $attributeValidatorHelper->validateScope($attribute, 'ecommerce')->shouldBeCalled();
        $attributeValidatorHelper->validateLocale($attribute, 'en_US')->shouldBeCalled();

        $registry->get('text')->willReturn($productValueFactory);
        $productValueFactory->create($attribute, 'ecommerce', 'en_US')->willReturn($productValue);

        $this->create($attribute, 'ecommerce', 'en_US')->shouldReturn($productValue);
    }
}
