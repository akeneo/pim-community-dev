<?php

namespace spec\Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\Factory\ProductValue\SimpleProductValueFactory;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Prophecy\Argument;

class SimpleProductValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['text', 'number'], ProductValue::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SimpleProductValueFactory::class);
    }

    function it_supports_some_attribute_types()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('text')->shouldReturn(true);
        $this->supports('number')->shouldReturn(true);
    }

    function it_creates_a_simple_empty_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('simple_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_simple_localizable_and_scopable_empty_product_value(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US'
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('simple_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    public function getMatchers()
    {
        return [
            'haveAttribute' => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable' => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'    => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'    => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'   => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'       => function ($subject) {
                return null === $subject->getData();
            },
        ];
    }
}
