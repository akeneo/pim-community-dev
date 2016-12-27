<?php

namespace spec\Pim\Component\Catalog\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
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

        $productValue = $this->createEmpty(
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
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn(new Locale());
        $localeRepository->findOneByIdentifier('en_US')->willReturn(new Channel());

        $productValue = $this->createEmpty(
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
        ChannelRepositoryInterface $channelRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(true);
        $channelRepository->findOneByIdentifier('mail')->willReturn(null);

        $this->shouldThrow('\InvalidArgumentException')->duringCreateEmpty(
            $attribute,
            'mail',
            'en_US'
        );
    }

    function it_does_not_create_a_simple_scopable_empty_product_value_with_invalid_locale_code(
        LocaleRepositoryInterface $localeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('simple_attribute');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn(null);

        $this->shouldThrow('\InvalidArgumentException')->duringCreateEmpty(
            $attribute,
            'mail',
            'en_US'
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
