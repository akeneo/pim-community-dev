<?php

namespace spec\Pim\Component\Catalog\Factory\ProductValue;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValue\MediaProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Prophecy\Argument;

class MediaProductValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ProductValue::class, Argument::any());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaProductValueFactory::class);
    }

    function it_creates_an_empty_file_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_file');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_image')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('file_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_file');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('file_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_file_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_file');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_image')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('file_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_file');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('file_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_file_product_value(AttributeInterface $attribute, FileInfoInterface $file)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_file');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_image')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('file_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_file');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            $file
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('file_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveMedia($file);
    }

    function it_creates_a_localizable_and_scopable_file_product_value(
        AttributeInterface $attribute,
        FileInfoInterface $file
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_file');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_image')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('file_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_file');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            $file
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('file_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveMedia($file);
    }

    function it_creates_an_empty_image_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('image_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_image_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('image_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_an_image_product_value(AttributeInterface $attribute, FileInfoInterface $image)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            $image
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('image_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveMedia($image);
    }

    function it_creates_a_localizable_and_scopable_image_product_value(
        AttributeInterface $attribute,
        FileInfoInterface $image
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            $image
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('image_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveMedia($image);
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
            'haveMedia'     => function ($subject, $media) {
                return $media === $subject->getData();
            },
        ];
    }
}
