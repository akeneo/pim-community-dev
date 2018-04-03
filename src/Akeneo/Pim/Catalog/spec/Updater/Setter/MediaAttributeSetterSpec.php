<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Prophecy\Argument;

class MediaAttributeSetterSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesBuilderInterface $builder,
        FileStorerInterface $storer,
        FileInfoRepositoryInterface $repository
    ) {
        $this->beConstructedWith(
            $builder,
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
                'Pim\Component\Catalog\Updater\Setter\MediaAttributeSetter',
                'path/to/unknown/file'
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_does_not_create_product_value_if_attribute_media_data_is_null(
        $repository,
        $storer,
        $builder,
        ProductInterface $product,
        AttributeInterface $fileAttribute,
        AttributeInterface $imageAttribute
    ) {
        $fileAttribute->getCode()->willReturn('file');
        $imageAttribute->getCode()->willReturn('image');

        $repository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $storer->store(Argument::cetera())->shouldNotBeCalled();

        $builder->addOrReplaceValue($product, $fileAttribute, null, null, null);
        $builder->addOrReplaceValue($product, $imageAttribute, 'en_US', 'ecommerce', null);

        $this->setAttributeData($product, $fileAttribute, null, ['locale' => null, 'scope' => null]);
        $this->setAttributeData($product, $imageAttribute, null, ['locale' => 'en_US', 'scope' => 'ecommerce']);
    }

    function it_sets_an_attribute_data_media_to_a_product(
        $repository,
        $storer,
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product,
        FileInfoInterface $fileInfo
    ) {
        $data = realpath(__DIR__ . '/../../../../../../../features/Context/fixtures/akeneo.jpg');
        $attribute->getCode()->willReturn('attributeCode');

        $repository->findOneByIdentifier(Argument::any())->willReturn(null);
        $storer->store(Argument::cetera())->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn($data);

        $builder->addOrReplaceValue($product, $attribute, 'fr_FR', 'mobile', $data)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_sets_an_attribute_data_media_to_a_product_that_has_no_value(
        $storer,
        $repository,
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product,
        ValueInterface $value,
        FileInfoInterface $fileInfo
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn(null);

        $data = realpath(__DIR__.'/../../../../../../../features/Context/fixtures/akeneo.jpg');

        $repository->findOneByIdentifier(Argument::any())->willReturn(null);
        $storer->store(Argument::cetera())->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn($data);

        $builder->addOrReplaceValue($product, $attribute, 'fr_FR', 'mobile', $data)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_does_not_store_an_attribute_data_that_has_already_been_stored_as_media(
        $repository,
        $storer,
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product,
        ValueInterface $value,
        FileInfoInterface $fileInfo
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn($value);

        $data = '4/e/6/c/4e6cb2788fa565037745ee01e48102780cc4d52b_my_file.jpg';

        $repository->findOneByIdentifier(Argument::any())->willReturn($fileInfo);
        $storer->store(Argument::cetera())->shouldNotBeCalled();
        $fileInfo->getKey()->willReturn($data);

        $builder->addOrReplaceValue($product, $attribute, 'fr_FR', 'mobile', $data)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }
}
