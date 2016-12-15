<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MediaAttributeSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        FileStorerInterface $storer,
        FileInfoRepositoryInterface $repository
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $storer,
            $repository,
            ['pim_catalog_file', 'pim_catalog_image']
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
        $repository,
        $storer,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $mediaValue,
        FileInfoInterface $fileInfo
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $repository->findOneByIdentifier(Argument::any())->willReturn(null);
        $storer->store(Argument::cetera())->willReturn($fileInfo);

        $data = realpath(__DIR__.'/../../../../../../../features/Context/fixtures/akeneo.jpg');

        $attribute->getCode()->willReturn('front_view');
        $product->getValue('front_view', 'fr_FR', 'mobile')->willReturn($mediaValue);
        $mediaValue->setMedia($fileInfo)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_attribute_data_is_not_a_string(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = new \stdClass();

        $this->shouldThrow(
            InvalidArgumentException::stringExpected('attributeCode', 'setter', 'media', gettype($data))
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_is_not_a_valid_path(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'path/to/unknown/file';

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'attributeCode',
                'a valid pathname',
                'setter',
                'media',
                'path/to/unknown/file'
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_allows_setting_attribute_data_media_to_null(
        ProductInterface $product,
        AttributeInterface $file,
        AttributeInterface $image,
        ProductValueInterface $fileValue,
        ProductValueInterface $imageValue
    ) {
        $file->getCode()->willReturn('file');
        $image->getCode()->willReturn('image');

        $product->getValue('file', null, null)->shouldBeCalled()->willReturn($fileValue);
        $product->getValue('image', null, null)->shouldBeCalled()->willReturn($imageValue);

        $fileValue->setMedia(null)->shouldBeCalled();
        $imageValue->setMedia(null)->shouldBeCalled();

        $this->setAttributeData($product, $file, null, ['locale' => null, 'scope' => null]);
        $this->setAttributeData($product, $image, null, ['locale' => null, 'scope' => null]);
    }

    function it_sets_an_attribute_data_media_to_a_product(
        $repository,
        $storer,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $value,
        FileInfoInterface $fileInfo
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn($value);

        $data = realpath(__DIR__ . '/../../../../../../../features/Context/fixtures/akeneo.jpg');

        $repository->findOneByIdentifier(Argument::any())->willReturn(null);
        $storer->store(Argument::cetera())->willReturn($fileInfo);
        $value->setMedia($fileInfo)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_sets_an_attribute_data_media_to_a_product_that_has_no_value(
        $storer,
        $repository,
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $value,
        FileInfoInterface $fileInfo
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn(null);

        $data = realpath(__DIR__.'/../../../../../../../features/Context/fixtures/akeneo.jpg');

        $builder->addProductValue($product, $attribute, Argument::cetera())->shouldBeCalled()->willReturn($value);
        $repository->findOneByIdentifier(Argument::any())->willReturn(null);
        $storer->store(Argument::cetera())->willReturn($fileInfo);

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_does_not_store_an_attribute_data_that_has_already_been_stored_as_media(
        $repository,
        $storer,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $value,
        FileInfoInterface $fileInfo
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn($value);

        $data = '4/e/6/c/4e6cb2788fa565037745ee01e48102780cc4d52b_my_file.jpg';

        $repository->findOneByIdentifier(Argument::any())->willReturn($fileInfo);
        $storer->store(Argument::cetera())->shouldNotBeCalled();
        $value->setMedia($fileInfo)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }
}
