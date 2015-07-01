<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MediaAttributeSetterSpec extends ObjectBehavior
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
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\Setter\MediaAttributeSetter');
    }

    function it_supports_media_attributes(
        AttributeInterface $imageAttribute,
        AttributeInterface $fileAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $imageAttribute->getAttributeType()->willReturn('pim_catalog_image');
        $this->supportsAttribute($imageAttribute)->shouldReturn(true);

        $fileAttribute->getAttributeType()->willReturn('pim_catalog_file');
        $this->supportsAttribute($fileAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_an_attribute_data(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $mediaValue,
        ProductMediaInterface $media
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $data = [
            'originalFilename' => 'akeneo',
            'filePath' => realpath(__DIR__.'/../../../../../../features/Context/fixtures/akeneo.jpg'),
        ];

        $attribute->getCode()->willReturn('front_view');
        $product->getValue('front_view', 'fr_FR', 'mobile')->willReturn($mediaValue);
        $mediaValue->getMedia()->willReturn($media);
        $mediaValue->setMedia($media)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_attribute_data_is_not_an_array_or_null(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = new \stdClass();

        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('attributeCode', 'setter', 'media', gettype($data))
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_filepath_is_missing(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [
            'originalFilename' => 'image',
        ];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('attributeCode', 'filePath', 'setter', 'media', print_r($data, true))
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_original_filename_is_missing(
        AttributeInterface $attribute,
        ProductInterface $product
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
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_is_not_a_valid_path(
        AttributeInterface $attribute,
        ProductInterface $product
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
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_allows_setting_attribute_data_media_to_null(
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

        $this->setAttributeData($product, $file, null, ['locale' => null, 'scope' => null]);
        $this->setAttributeData($product, $image, ['originalFilename' => null, 'filePath' => null], ['locale' => null, 'scope' => null]);
    }

    function it_sets_a_attribute_data_media_to_a_product_that_already_has_a_media(
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
        $media->setOriginalFilename('akeneo')->shouldBeCalled();
        $manager->handleProductMedias($product)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_sets_a_attribute_data_media_to_a_product_that_has_no_media(
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
        $manager->handleProductMedias($product)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_sets_an_attribute_data_media_to_a_product_that_has_no_value(
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
        $manager->handleProductMedias($product)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }
}
