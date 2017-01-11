<?php

namespace spec\Pim\Component\Catalog\Factory\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValue\OptionProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Prophecy\Argument;

class OptionProductValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ProductValue::class, Argument::any());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionProductValueFactory::class);
    }

    function it_creates_an_empty_simple_select_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_simpleselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_simpleselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_simple_select_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_simpleselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_simpleselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_simple_select_product_value(
        AttributeInterface $attribute,
        AttributeOptionInterface $option
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_simpleselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_simpleselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            $option
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOption($option);
    }

    function it_creates_a_localizable_and_scopable_simple_select_product_value(
        AttributeInterface $attribute,
        AttributeOptionInterface $option
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_simpleselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_simpleselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            $option
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('simple_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveTheOption($option);
    }

    public function getMatchers()
    {
        return [
            'haveAttribute'  => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable'  => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'     => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'     => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'    => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'        => function ($subject) {
                return null === $subject->getData();
            },
            'haveTheOption'  => function ($subject, $option) {
                return $option === $subject->getData();
            },
        ];
    }
}
