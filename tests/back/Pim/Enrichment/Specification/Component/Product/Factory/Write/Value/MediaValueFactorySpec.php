<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\MediaValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Prophecy\Argument;

class MediaValueFactorySpec extends ObjectBehavior
{
    function let(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->beConstructedWith($fileInfoRepository, MediaValue::class, Argument::any());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaValueFactory::class);
    }

    function it_throws_an_exception_when_creating_an_empty_file_product_value($fileInfoRepository, AttributeInterface $attribute)
    {
        $this->beConstructedWith($fileInfoRepository, MediaValue::class, 'pim_catalog_file');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_image')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('file_attribute');
        $attribute->getType()->willReturn('pim_catalog_file');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $fileInfoRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_a_file_product_value(
        $fileInfoRepository,
        AttributeInterface $attribute,
        FileInfoInterface $file
    ) {
        $this->beConstructedWith($fileInfoRepository, MediaValue::class, 'pim_catalog_file');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_image')->shouldReturn(false);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('file_attribute');
        $attribute->getType()->willReturn('pim_catalog_file');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $fileInfoRepository->findOneByIdentifier('foobar')->willReturn($file);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(MediaValue::class);
        $productValue->shouldHaveAttribute('file_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveMedia($file);
    }

    function it_creates_a_localizable_and_scopable_file_product_value(
        $fileInfoRepository,
        AttributeInterface $attribute,
        FileInfoInterface $file
    ) {
        $this->beConstructedWith($fileInfoRepository, MediaValue::class, 'pim_catalog_file');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_image')->shouldReturn(false);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('file_attribute');
        $attribute->getType()->willReturn('pim_catalog_file');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $fileInfoRepository->findOneByIdentifier('foobar')->willReturn($file);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(MediaValue::class);
        $productValue->shouldHaveAttribute('file_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveMedia($file);
    }

    function it_throws_an_exception_when_creating_an_empty_image_product_value($fileInfoRepository, AttributeInterface $attribute)
    {
        $this->beConstructedWith($fileInfoRepository, MediaValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $fileInfoRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_an_image_product_value(
        $fileInfoRepository,
        AttributeInterface $attribute,
        FileInfoInterface $image
    ) {
        $this->beConstructedWith($fileInfoRepository, MediaValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $fileInfoRepository->findOneByIdentifier('foobar')->willReturn($image);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(MediaValue::class);
        $productValue->shouldHaveAttribute('image_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveMedia($image);
    }

    function it_creates_a_localizable_and_scopable_image_product_value(
        $fileInfoRepository,
        AttributeInterface $attribute,
        FileInfoInterface $image
    ) {
        $this->beConstructedWith($fileInfoRepository, MediaValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $fileInfoRepository->findOneByIdentifier('foobar')->willReturn($image);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            'foobar'
        );

        $productValue->shouldReturnAnInstanceOf(MediaValue::class);
        $productValue->shouldHaveAttribute('image_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveMedia($image);
    }

    function it_throws_an_exception_if_provided_data_is_not_a_string(
        $fileInfoRepository,
        AttributeInterface $attribute
    ) {

        $this->beConstructedWith($fileInfoRepository, MediaValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $fileInfoRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $exception = InvalidPropertyTypeException::stringExpected(
            'image_attribute',
            MediaValueFactory::class,
            []
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', []]);
    }

    function it_throws_an_exception_if_provided_data_is_not_an_existing_fileinfo_key(
        $fileInfoRepository,
        AttributeInterface $attribute
    ) {

        $this->beConstructedWith($fileInfoRepository, MediaValue::class, 'pim_catalog_image');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_file')->shouldReturn(false);
        $this->supports('pim_catalog_image')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('image_attribute');
        $attribute->getType()->willReturn('pim_catalog_image');
        $attribute->getBackendType()->willReturn('media');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $fileInfoRepository->findOneByIdentifier('foo/bar.txt')->willReturn(null);

        $exception = InvalidPropertyException::validEntityCodeExpected(
            'image_attribute',
            'fileinfo key',
            'The media does not exist',
            MediaValueFactory::class,
            'foo/bar.txt'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', 'foo/bar.txt']);
    }

    public function getMatchers(): array
    {
        return [
            'haveAttribute' => function ($subject, $attributeCode) {
                return $subject->getAttributeCode() === $attributeCode;
            },
            'beLocalizable' => function ($subject) {
                return $subject->isLocalizable();
            },
            'haveLocale'    => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocaleCode();
            },
            'beScopable'    => function ($subject) {
                return $subject->isScopable();
            },
            'haveChannel'   => function ($subject, $channelCode) {
                return $channelCode === $subject->getScopeCode();
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
