<?php

namespace spec\Pim\Component\Catalog\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class ProductValueFactorySpec extends ObjectBehavior
{
    function let(ChannelRepositoryInterface $channelRepository, LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($channelRepository, $localeRepository, ProductValue::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueFactory::class);
    }

    function it_creates_a_simple_empty_product_value(
        AttributeInterface $attribute
    ) {
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

    function it_creates_a_simple_localizable_and_scopable_empty_product_value(
        $channelRepository,
        $localeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $channelRepository->getChannelCodes()->willReturn(['ecommerce']);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US']);

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

    function it_does_not_create_a_simple_scopable_empty_product_value_with_invalid_scope_code(
        $channelRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(true);
        $channelRepository->getChannelCodes()->willReturn(['ecommerce', 'mobile']);

        $this->shouldThrow('\InvalidArgumentException')->duringCreate(
            $attribute,
            'mail',
            'en_US'
        );
    }

    function it_does_not_create_a_simple_scopable_empty_product_value_with_no_scope_code(
        $channelRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(true);
        $channelRepository->getChannelCodes()->willReturn(['ecommerce', 'mobile']);

        $this->shouldThrow('\InvalidArgumentException')->duringCreate(
            $attribute,
            null,
            'en_US'
        );
    }

    function it_does_not_create_a_simple_scopable_empty_product_value_with_invalid_locale_code(
        $localeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR']);

        $this->shouldThrow('\InvalidArgumentException')->duringCreate(
            $attribute,
            'mail',
            'en_US'
        );
    }

    function it_does_not_create_a_simple_scopable_empty_product_value_with_no_locale_code(
        $localeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $localeRepository->getActivatedLocaleCodes()->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->duringCreate(
            $attribute,
            'mail',
            null
        );
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
