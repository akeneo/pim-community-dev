<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MediaValueSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        MediaManager $manager,
        MediaFactory $mediaFactory,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $manager,
            $mediaFactory,
            ['pim_catalog_file', 'pim_catalog_image'],
            '../../../../../../app/uploads/product/'
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\Setter\MediaValueSetter');
    }

    function it_supports_media_attributes(
        AttributeInterface $imageAttribute,
        AttributeInterface $fileAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $imageAttribute->getAttributeType()->willReturn('pim_catalog_image');
        $this->supports($imageAttribute)->shouldReturn(true);

        $fileAttribute->getAttributeType()->willReturn('pim_catalog_file');
        $this->supports($fileAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_a_value(
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $data = [
            'originalFilename' => 'akeneo',
            'filePath' => realpath(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
        ];

        $this->setValue([], $attribute, $data, 'fr_FR', 'mobile');
    }

    function it_throws_an_error_if_data_is_not_an_array_or_null(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = new \stdClass();

        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('attributeCode', 'setter', 'media', gettype($data))
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_filepath_is_missing(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [
            'originalFilename' => 'image',
        ];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('attributeCode', 'filePath', 'setter', 'media', print_r($data, true))
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_original_filename_is_missing(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [
            'filePath' => realpath(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
        ];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected(
                'attributeCode',
                'originalFilename',
                'setter',
                'media',
                print_r($data, true)
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_data_is_not_a_valid_path(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [
            'filePath'         => 'path/to/unknown/file',
            'originalFilename' => 'image',
        ];

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'attributeCode',
                'a valid file path',
                'setter',
                'media',
                '../../../../../../app/uploads/product/path/to/unknown/file'
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_allows_setting_media_to_null(
        ProductInterface $product,
        AttributeInterface $file,
        AttributeInterface $image,
        ProductValueInterface $fileValue,
        ProductValueInterface $imageValue,
        ProductMediaInterface $fileMedia,
        ProductMediaInterface $imageMedia
    ) {
        $file->getCode()->willReturn('file');
        $image->getCode()->willReturn('image');

        $product->getValue('file', null, null)->shouldBeCalled()->willReturn($fileValue);
        $product->getValue('image', null, null)->shouldBeCalled()->willReturn($imageValue);

        $fileValue->getMedia()->willReturn($fileMedia);
        $imageValue->getMedia()->willReturn($imageMedia);

        $fileMedia->setRemoved(true)->shouldBeCalled();
        $imageMedia->setRemoved(true)->shouldBeCalled();

        $fileValue->setMedia($fileMedia)->shouldBeCalled();
        $imageValue->setMedia($imageMedia)->shouldBeCalled();

        $this->setValue([$product], $file, null);
        $this->setValue([$product], $image, ['originalFilename' => null, 'filePath' => null]);
    }

    function it_sets_a_media_to_a_product_that_already_has_a_media(
        $manager,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductMediaInterface $media
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn($value);
        $value->getMedia()->willReturn($media);

        $data = [
            'originalFilename' => 'akeneo',
            'filePath' => realpath(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
        ];

        $value->setMedia($media)->shouldBeCalled();
        $media->setFile(Argument::any())->shouldBeCalled();
        $manager->handleAllProductsMedias([$product])->shouldBeCalled();

        $this->setValue([$product], $attribute, $data, 'fr_FR', 'mobile');
    }

    function it_sets_a_media_to_a_product_that_has_no_media(
        $manager,
        $mediaFactory,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductMediaInterface $media
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn($value);
        $value->getMedia()->willReturn(null);
        $mediaFactory->createMedia(Argument::any())->shouldBeCalled()->willReturn($media);

        $data = [
            'originalFilename' => 'akeneo',
            'filePath' => realpath(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
        ];

        $value->setMedia($media)->shouldBeCalled();
        $manager->handleAllProductsMedias([$product])->shouldBeCalled();

        $this->setValue([$product], $attribute, $data, 'fr_FR', 'mobile');
    }

    function it_sets_a_media_to_a_product_that_has_no_value(
        $manager,
        $builder,
        $mediaFactory,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductMediaInterface $media
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn(null);
        $value->getMedia()->willReturn(null);

        $data = [
            'originalFilename' => 'akeneo',
            'filePath' => realpath(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
        ];

        $builder->addProductValue($product, $attribute, Argument::cetera())->shouldBeCalled()->willReturn($value);
        $mediaFactory->createMedia(Argument::any())->shouldBeCalled()->willReturn($media);
        $value->setMedia($media)->shouldBeCalled($value);
        $manager->handleAllProductsMedias([$product])->shouldBeCalled();

        $this->setValue([$product], $attribute, $data, 'fr_FR', 'mobile');
    }
}
