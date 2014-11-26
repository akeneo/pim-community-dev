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
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Prophecy\Argument;

class MediaValueSetterSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, MediaManager $manager, MediaFactory $mediaFactory)
    {
        $this->beConstructedWith($builder, $manager, $mediaFactory, ['pim_catalog_file', 'pim_catalog_image']);
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

    function it_returns_supported_attributes_types()
    {
        $this->getSupportedTypes()->shouldReturn(['pim_catalog_file', 'pim_catalog_image']);
    }

    function it_throws_an_error_if_data_is_not_an_array(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = new \stdClass();

        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('attributeCode', 'setter', 'media')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_filepath_is_missing(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = [
            'originalFilename' => 'image.jpg',
        ];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('attributeCode', 'filePath', 'setter', 'media')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_original_filename_is_missing(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = [
            'filePath' => realpath(__DIR__ . '/../../../../../../features/Context/fixtures/akeneo.jpg'),
        ];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('attributeCode', 'originalFilename', 'setter', 'media')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_data_is_not_a_valid_path(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = [
            'filePath' => 'path/to/unknown/file',
            'originalFilename' => 'image.jpg',
        ];

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'attributeCode',
                'a valid file path ("path/to/unknown/file" given)',
                'setter',
                'media'
            )
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_a_media_to_a_product_that_already_has_a_media(
        $manager,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductMediaInterface $media
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn($value);
        $value->getMedia()->willReturn($media);

        $data = [
            'originalFilename' => 'image.jpg',
            'filePath' => realpath(__DIR__ . '/../../../../../../features/Context/fixtures/akeneo.jpg'),
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
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn($value);
        $value->getMedia()->willReturn(null);
        $mediaFactory->createMedia(Argument::any())->shouldBeCalled()->willReturn($media);

        $data = [
            'originalFilename' => 'image.jpg',
            'filePath' => realpath(__DIR__ . '/../../../../../../features/Context/fixtures/akeneo.jpg'),
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
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn(null);
        $value->getMedia()->willReturn(null);

        $data = [
            'originalFilename' => 'image.jpg',
            'filePath' => realpath(__DIR__ . '/../../../../../../features/Context/fixtures/akeneo.jpg'),
        ];

        $builder->addProductValue($product, $attribute, Argument::cetera())->shouldBeCalled()->willReturn($value);
        $mediaFactory->createMedia(Argument::any())->shouldBeCalled()->willReturn($media);
        $value->setMedia($media)->shouldBeCalled($value);
        $manager->handleAllProductsMedias([$product])->shouldBeCalled();

        $this->setValue([$product], $attribute, $data, 'fr_FR', 'mobile');
    }


}
