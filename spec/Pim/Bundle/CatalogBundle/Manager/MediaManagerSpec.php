<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;

class MediaManagerSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem, MediaFactory $factory, ManagerRegistry $registry)
    {
        $path = realpath(__DIR__.'/../../../../../features/Context/fixtures/');
        $this->beConstructedWith($filesystem, $path, $factory, $registry);
    }

    function it_handles_new_product_media_upload($filesystem, ProductMedia $media, File $newFile)
    {
        $media->getFile()->willReturn($newFile);
        $media->getFilename()->willReturn('my-new-file.jpg');

        $filesystem->has('my-new-file.jpg')->willReturn(false);
        $newFile->getFilename()->willReturn('my-new-file.jpg');

        $pathname = realpath(__DIR__.'/../../../../../features/Context/fixtures/akeneo.jpg');
        $newFile->getPathname()->willReturn($pathname);

        // write a fake file in tmp
        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);
        $fs->write('tmp-phpspec', '', true);

        $filesystem->write('prefix-my-new-file.jpg', Argument::any(), false)->shouldBeCalled();
        $media->setOriginalFilename('my-new-file.jpg')->shouldBeCalled();
        $media->setFilename('prefix-my-new-file.jpg')->shouldBeCalled();
        $media->setFilePath(null)->shouldBeCalled();
        $newFile->getMimeType()->willReturn('jpg');
        $media->setMimeType('jpg')->shouldBeCalled();
        $media->resetFile()->shouldBeCalled();

        $this->handle($media, 'prefix');
    }

    function it_handles_existing_product_media_upload($filesystem, ProductMedia $media, File $newFile)
    {
        $media->getFile()->willReturn($newFile);
        $media->getFilename()->willReturn('akeneo.jpg');

        $filesystem->has('akeneo.jpg')->willReturn(true);
        $newFile->getFilename()->willReturn('akeneo.jpg');

        // delete the existing file
        $filesystem->has('akeneo.jpg')->willReturn(true);
        $filesystem->delete('akeneo.jpg')->willReturn(true);
        $media->setOriginalFilename(null)->shouldBeCalled();
        $media->setFilename(null)->shouldBeCalled();
        $media->setFilePath(null)->shouldBeCalled();
        $media->setMimeType(null)->shouldBeCalled();

        // upload
        $newFile->getPathname()->willReturn('/tmp/tmp-phpspec');

        // write a fake file in tmp
        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);
        $fs->write('tmp-phpspec', '', true);

        $filesystem->write('prefix-akeneo.jpg', '', false)->shouldBeCalled();
        $media->setOriginalFilename('akeneo.jpg')->shouldBeCalled();
        $media->setFilename('prefix-akeneo.jpg')->shouldBeCalled();
        $media->setFilePath(Argument::any())->shouldBeCalled();
        $newFile->getMimeType()->willReturn('jpg');
        $media->setMimeType('jpg')->shouldBeCalled();
        $media->resetFile()->shouldBeCalled();

        $this->handle($media, 'prefix');
    }

    function it_copies_media(
        $filesystem,
        AbstractProductMedia $media,
        ProductValue $value,
        AbstractAttribute $attribute,
        ProductInterface $product
    ) {
        $filesystem->has('akeneo.jpg')->willReturn(true);

        $media->getFilePath()->willReturn('/tmp');
        $media->getValue()->willReturn($value);
        $media->getFilename()->willReturn('akeneo.jpg');
        $value->getAttribute()->willReturn($attribute);
        $value->getEntity()->willReturn($product);
        $product->getIdentifier()->willReturn('my-sku');
        $attribute->getCode()->willReturn('thumbnail');
        $attribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('en_US');
        $attribute->isScopable()->willReturn(true);
        $value->getScope()->willReturn('mobile');
        $media->getOriginalFilename()->willReturn('akeneo.jpg');

        $this->copy($media, '/tmp')->shouldReturn(true);
    }

    function it_returns_false_if_copie_media_fail(
        $filesystem,
        AbstractProductMedia $media,
        ProductValue $value,
        AbstractAttribute $attribute,
        ProductInterface $product
    ) {
        $filesystem->has('akeneo.jpg')->willReturn(false);

        $media->getFilePath()->willReturn('/tmp');
        $media->getValue()->willReturn($value);
        $media->getFilename()->willReturn('akeneo.jpg');
        $value->getAttribute()->willReturn($attribute);
        $value->getEntity()->willReturn($product);
        $product->getIdentifier()->willReturn('my-sku');
        $attribute->getCode()->willReturn('thumbnail');
        $attribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('en_US');
        $attribute->isScopable()->willReturn(true);
        $value->getScope()->willReturn('mobile');
        $media->getOriginalFilename()->willReturn('akeneo.jpg');

        $this->copy($media, '/tmp')->shouldReturn(false);
    }

    function it_duplicates_product_media($filesystem, ProductMedia $source, ProductMedia $target, File $newFile)
    {
        // write a fake file in tmp
        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);
        $fs->write('akeneo.jpg', '', true);

        $source->getFilename()->willReturn('akeneo.jpg');
        $filesystem->has('akeneo.jpg')->willReturn(true);
        $target->setFile(Argument::any())->shouldBeCalled();

        $target->getFile()->shouldBeCalled()->willReturn($newFile);
        $newFile->getPathname()->willReturn('/tmp/tmp-phpspec');
        $filesystem->write('prefix-akeneo.jpg', '', false)->shouldBeCalled();
        $newFile->getFilename()->willReturn('akeneo.jpg');
        $target->setOriginalFilename('akeneo.jpg')->shouldBeCalled();

        $filesystem->has(null)->willReturn(true);
        $target->setFilename('prefix-akeneo.jpg')->shouldBeCalled();
        $target->setFilePath(Argument::any())->shouldBeCalled();
        $newFile->getMimeType()->willReturn('jpg');
        $target->setMimeType('jpg')->shouldBeCalled();
        $target->resetFile()->shouldBeCalled();

        $target->getFilename()->shouldBeCalled();


        $source->getOriginalFilename()->willReturn('akeneo.jpg');

        $this->duplicate($source, $target, 'prefix');
    }

    function it_provides_export_path($filesystem, AbstractProductMedia $media, ProductValue $value, AbstractAttribute $attribute, ProductInterface $product)
    {
        $filesystem->has('akeneo.jpg')->willReturn(true);

        $media->getFilePath()->willReturn('/tmp');
        $media->getValue()->willReturn($value);
        $media->getFilename()->willReturn('akeneo.jpg');
        $value->getAttribute()->willReturn($attribute);
        $value->getEntity()->willReturn($product);
        $product->getIdentifier()->willReturn('my-sku');
        $attribute->getCode()->willReturn('thumbnail');
        $attribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('en_US');
        $attribute->isScopable()->willReturn(true);
        $value->getScope()->willReturn('mobile');
        $media->getOriginalFilename()->willReturn('akeneo.jpg');

        $this->getExportPath($media)->shouldReturn('files/my-sku/thumbnail/en_US/mobile/akeneo.jpg');
    }

    function it_generates_filename_prefix(ProductInterface $product, ProductValue $value, AbstractAttribute $attribute)
    {
        $product->getId()->willReturn(42);
        $product->getIdentifier()->shouldBeCalled()->willReturn('identifier');
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->shouldBeCalled()->willReturn('code');
        $value->getLocale()->shouldBeCalled()->willReturn('en_US');
        $value->getScope()->shouldBeCalled()->willReturn('mobile');

        $this->generateFilenamePrefix($product, $value);
    }
}
