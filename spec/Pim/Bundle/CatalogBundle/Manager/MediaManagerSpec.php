<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManagerSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem, MediaFactory $factory, ManagerRegistry $registry)
    {
        $path = realpath(__DIR__.'/../../../../../features/Context/fixtures/');
        $this->beConstructedWith($filesystem, $path, $factory, $registry);
    }

    function it_handles_new_product_media_upload($filesystem, ProductMediaInterface $media, File $newFile)
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

    function it_handles_existing_product_media_upload($filesystem, ProductMediaInterface $media, File $newFile)
    {
        $media->getFile()->willReturn($newFile);
        $media->getFilename()->willReturn('akeneo.jpg');

        $filesystem->has('akeneo.jpg')->willReturn(true);
        $newFile->getFilename()->willReturn('akeneo.jpg');

        // delete the existing file
        $filesystem->has('akeneo.jpg')->willReturn(true);
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

    function it_duplicates_product_media($filesystem, ProductMediaInterface $source, ProductMediaInterface $target, File $newFile)
    {
        $source->getFilePath()->willReturn('/tmp/tmp-phpspec');
        $target->setFile(Argument::any())->shouldBeCalled();
        $source->getOriginalFilename()->willReturn('akeneo.jpg');

        // upload
        $target->getFile()->willReturn($newFile);
        $newFile->getPathname()->willReturn('/tmp/tmp-phpspec');

        // write a fake file in tmp
        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);
        $fs->write('tmp-phpspec', '', true);

        $newFile->getFilename()->willReturn('akeneo.jpg');
        $filesystem->write('prefix-akeneo.jpg', '', false)->shouldBeCalled();
        $target->setOriginalFilename('akeneo.jpg')->shouldBeCalled();
        $target->setFilename('prefix-akeneo.jpg')->shouldBeCalled();
        $filesystem->has('akeneo.jpg')->willReturn(true);
        $target->getFilename()->willReturn('akeneo.jpg');
        $target->setFilePath(Argument::any())->shouldBeCalled();
        $newFile->getMimeType()->willReturn('jpg');
        $target->setMimeType('jpg')->shouldBeCalled();
        $target->resetFile()->shouldBeCalled();

        // update original file name
        $source->getOriginalFilename()->willReturn('akeneo.jpg');
        $target->setOriginalFilename('akeneo.jpg')->shouldBeCalled();

        $this->duplicate($source, $target, 'prefix');
    }

    function it_provides_export_path(ProductMediaInterface $media, ProductValueInterface $value, AttributeInterface $attribute, ProductInterface $product)
    {
        $media->getFilePath()->willReturn('my-path');
        $media->getValue()->willReturn($value);
        $value->getAttribute()->willReturn($attribute);
        $value->getEntity()->willReturn($product);
        $product->getIdentifier()->willReturn('my-sku');
        $attribute->getCode()->willReturn('thumbnail');
        $attribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('en_US');
        $attribute->isScopable()->willReturn(true);
        $value->getScope()->willReturn('mobile');
        $media->getOriginalFilename()->willReturn('my-file.jpg');

        $this->getExportPath($media)->shouldReturn('files/my-sku/thumbnail/en_US/mobile/my-file.jpg');
    }

    function it_generates_filename_prefix(ProductInterface $product, ProductValueInterface $value, AttributeInterface $attribute)
    {
        $product->getIdentifier()->shouldBeCalled();
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->shouldBeCalled();
        $value->getLocale()->shouldBeCalled();
        $value->getScope()->shouldBeCalled();

        $this->generateFilenamePrefix($product, $value);
    }

    function it_loads_file_into_a_media($filesystem, $factory, ProductMediaInterface $media)
    {
        $filesystem->has('preview.jpg')->willReturn(true);
        $filesystem->mimeType('preview.jpg')->willReturn('image/jpeg');

        $factory->createMedia()->willReturn($media);
        $media->setOriginalFilename('preview.jpg')->shouldBeCalled();
        $media->setFilename('preview.jpg')->shouldBeCalled();
        $media->setFilePath(Argument::any())->shouldBeCalled();
        $media->setMimeType('image/jpeg')->shouldBeCalled();

        $this->createFromFilename('preview.jpg')->shouldReturn($media);
    }

    function its_load_method_throw_exception_when_file_does_not_exist($filesystem, ProductMediaInterface $media)
    {
        $filesystem->has('readme.md')->willReturn(false);

        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringCreateFromFilename('readme.md');
    }
}
