<?php

namespace spec\Pim\Component\Catalog\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\Value\ValueFactoryInterface;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

class ValueFactorySpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith($attributeValidatorHelper, []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueFactory::class);
    }

    function it_creates_a_simple_empty_product_value(
        $attributeValidatorHelper,
        AttributeInterface $attribute,
        ValueFactoryInterface $productValueFactory,
        ValueInterface $productValue
    ) {
        $productValueFactory->supports('text')->willReturn(true);
        $this->registerFactory($productValueFactory);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->getType()->willReturn('text');

        $attributeValidatorHelper->validateLocale($attribute, null)->shouldBeCalled();
        $attributeValidatorHelper->validateScope($attribute, null)->shouldBeCalled();

        $productValueFactory->create($attribute, null, null, 'foobar')->willReturn($productValue);

        $this->create($attribute, null, null, 'foobar')->shouldReturn($productValue);
    }

    function it_creates_a_simple_localizable_and_scopable_empty_product_value(
        $attributeValidatorHelper,
        AttributeInterface $attribute,
        ValueFactoryInterface $productValueFactory,
        ValueInterface $productValue
    ) {
        $productValueFactory->supports('text')->willReturn(true);
        $this->registerFactory($productValueFactory);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->getType()->willReturn('text');

        $attributeValidatorHelper->validateScope($attribute, 'ecommerce')->shouldBeCalled();
        $attributeValidatorHelper->validateLocale($attribute, 'en_US')->shouldBeCalled();

        $productValueFactory->create($attribute, 'ecommerce', 'en_US', 'foobar')->willReturn($productValue);

        $this->create($attribute, 'ecommerce', 'en_US', 'foobar')->shouldReturn($productValue);
    }

    function it_throws_an_exception_when_there_is_no_registered_factory(
        ValueFactoryInterface $factory,
        AttributeInterface $attribute
    ) {
        $this->registerFactory($factory);
        $attribute->getType()->willReturn('text');

        $factory->supports('text')->willReturn(false);

        $this->shouldThrow('\OutOfBoundsException')->during('create', [$attribute, null, null, 'foobar']);
    }
}
