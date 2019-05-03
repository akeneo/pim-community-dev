<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AttributeSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\MediaAttributeSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use League\Flysystem\FilesystemInterface;
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
        FileStorerInterface $storer,
        FilesystemProvider $filesystemProvider,
        FileInfoRepositoryInterface $repository
    ) {
        $this->beConstructedWith(
            $builder,
            $storer,
            $repository,
            $filesystemProvider,
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
        ProductInterface $product,
        $filesystemProvider,
        FilesystemInterface $filesystem
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'path/to/unknown/file';

        $filesystemProvider->getFilesystem('pefTmpStorage')->willReturn($filesystem);
        $filesystem->has($data)->willReturn(false);

        $this->shouldThrow(
            InvalidPropertyException::validPathExpected(
                'attributeCode',
                MediaAttributeSetter::class,
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
        $filesystemProvider,
        FilesystemInterface $filesystem,
        AttributeInterface $attribute,
        ProductInterface $product,
        FileInfoInterface $fileInfo
    ) {
        $data = realpath(__DIR__ . '/../../../../../../../../../tests/legacy/features/Context/fixtures/akeneo.jpg');
        $attribute->getCode()->willReturn('attributeCode');

        $filesystemProvider->getFilesystem('pefTmpStorage')->willReturn($filesystem);
        $filesystem->has($data)->willReturn(true);

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
        $filesystemProvider,
        FilesystemInterface $filesystem,
        AttributeInterface $attribute,
        ProductInterface $product,
        FileInfoInterface $fileInfo
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $product->getValue('attributeCode', Argument::cetera())->willReturn(null);

        $data = realpath(__DIR__.'/../../../../../../../../../tests/legacy/features/Context/fixtures/akeneo.jpg');

        $filesystemProvider->getFilesystem('pefTmpStorage')->willReturn($filesystem);
        $filesystem->has($data)->willReturn(true);

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
