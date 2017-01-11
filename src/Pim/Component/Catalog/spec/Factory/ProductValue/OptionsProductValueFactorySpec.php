<?php

namespace spec\Pim\Component\Catalog\Factory\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValue\OptionsProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Prophecy\Argument;

class OptionsProductValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ProductValue::class, Argument::any());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionsProductValueFactory::class);
    }

    function it_creates_an_empty_multi_select_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_multiselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_multiselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            []
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_multi_select_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_multiselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_multiselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            []
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_multi_select_product_value(
        AttributeInterface $attribute,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_multiselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_multiselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            [$option1, $option2]
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveTheOptions([$option1, $option2]);
    }

    function it_creates_a_localizable_and_scopable_multi_select_product_value(
        AttributeInterface $attribute,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_multiselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_multiselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('multi_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $attribute->getBackendType()->willReturn('options');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            [$option1, $option2]
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveTheOptions([$option1, $option2]);
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
                return $subject->getData() instanceof ArrayCollection && [] === $subject->getData()->toArray();
            },
            'haveTheOptions' => function ($subject, $expectedOptions) {
                $result = false;
                $data = $subject->getData()->toArray();
                foreach ($data as $option) {
                    $result = in_array($option, $expectedOptions);
                }

                return $result && count($data) === count($expectedOptions);
            },
        ];
    }
}
