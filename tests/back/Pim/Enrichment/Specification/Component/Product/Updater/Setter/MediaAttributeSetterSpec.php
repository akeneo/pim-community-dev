<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AttributeSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\MediaAttributeSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Prophecy\Argument;

class MediaAttributeSetterSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesBuilderInterface $builder,
        FileInfoRepositoryInterface $fileInfoRepository
    ) {
        $this->beConstructedWith(
            $builder,
            $fileInfoRepository,
            ['pim_catalog_file', 'pim_catalog_image']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement(SetterInterface::class);
        $this->shouldImplement(AttributeSetterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaAttributeSetter::class);
    }

    function it_supports_media_attributes(
        AttributeInterface $imageAttribute,
        AttributeInterface $fileAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $imageAttribute->getType()->willReturn('pim_catalog_image');
        $this->supportsAttribute($imageAttribute)->shouldReturn(true);

        $fileAttribute->getType()->willReturn('pim_catalog_file');
        $this->supportsAttribute($fileAttribute)->shouldReturn(true);

        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_throws_an_error_if_attribute_data_is_not_a_valid_path(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'path/to/unknown/file';

        $this->shouldThrow(
            InvalidPropertyException::validPathExpected(
                'attributeCode',
                MediaAttributeSetter::class,
                'path/to/unknown/file'
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_does_not_create_product_value_if_attribute_media_data_is_null(
        $fileInfoRepository,
        $builder,
        ProductInterface $product,
        AttributeInterface $fileAttribute,
        AttributeInterface $imageAttribute
    ) {
        $fileAttribute->getCode()->willReturn('file');
        $imageAttribute->getCode()->willReturn('image');

        $fileInfoRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $builder->addOrReplaceValue($product, $fileAttribute, null, null, null);
        $builder->addOrReplaceValue($product, $imageAttribute, 'en_US', 'ecommerce', null);

        $this->setAttributeData($product, $fileAttribute, null, ['locale' => null, 'scope' => null]);
        $this->setAttributeData($product, $imageAttribute, null, ['locale' => 'en_US', 'scope' => 'ecommerce']);
    }

    function it_sets_an_attribute_data_media_to_a_product(
        $fileInfoRepository,
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product,
        FileInfoInterface $fileInfo
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $fileInfoRepository->findOneByIdentifier(Argument::any())->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('file/key');

        $builder->addOrReplaceValue($product, $attribute, 'fr_FR', 'mobile', 'file/key')->shouldBeCalled();

        $this->setAttributeData($product, $attribute, 'file/key', ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }
}
