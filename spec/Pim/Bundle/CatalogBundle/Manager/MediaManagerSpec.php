<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManagerSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem)
    {
        $this->beConstructedWith($filesystem, '/tmp/pim-ce');
    }

    function it_handles_new_product_media_upload($filesystem, ProductMediaInterface $media, File $newFile)
    {
        $media->getFile()->willReturn($newFile);
        $media->getFilename()->willReturn('my-new-file.jpg');

        $filesystem->has('my-new-file.jpg')->willReturn(false);
        $newFile->getFilename()->willReturn('my-new-file.jpg');
        $newFile->getPathname()->willReturn('/tmp/tmp-phpspec');

        // write a fake file in tmp
        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);
        $fs->write('tmp-phpspec', '', true);

        $filesystem->write('prefix-my-new-file.jpg', '', false)->shouldBeCalled();
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
        $media->getFilename()->willReturn('my-new-file.jpg');

        $filesystem->has('my-new-file.jpg')->willReturn(true);
        $newFile->getFilename()->willReturn('my-new-file.jpg');

        // delete the existing file
        $filesystem->has('my-new-file.jpg')->willReturn(true);
        $filesystem->delete('my-new-file.jpg')->shouldBeCalled();
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

        $filesystem->write('prefix-my-new-file.jpg', '', false)->shouldBeCalled();
        $media->setOriginalFilename('my-new-file.jpg')->shouldBeCalled();
        $media->setFilename('prefix-my-new-file.jpg')->shouldBeCalled();
        $media->setFilePath('/tmp/pim-ce/my-new-file.jpg')->shouldBeCalled();
        $newFile->getMimeType()->willReturn('jpg');
        $media->setMimeType('jpg')->shouldBeCalled();
        $media->resetFile()->shouldBeCalled();

        $this->handle($media, 'prefix');
    }

    function it_duplicates_product_media($filesystem, ProductMediaInterface $source, ProductMediaInterface $target, File $newFile)
    {
        $source->getFilePath()->willReturn('/tmp/tmp-phpspec');
        $target->setFile(Argument::any())->shouldBeCalled();
        $source->getOriginalFilename()->willReturn('my-source-file.jpg');

        // upload
        $target->getFile()->willReturn($newFile);
        $newFile->getPathname()->willReturn('/tmp/tmp-phpspec');

        // write a fake file in tmp
        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);
        $fs->write('tmp-phpspec', '', true);

        $filesystem->write('prefix-my-source-file.jpg', '', false)->shouldBeCalled();

        $newFile->getFilename()->willReturn('my-source-file.jpg');
        $target->setOriginalFilename('my-source-file.jpg')->shouldBeCalled();
        $target->setFilename('prefix-my-source-file.jpg')->shouldBeCalled();
        $filesystem->has('prefix-my-source-file.jpg')->willReturn(true);
        $target->getFilename()->willReturn('prefix-my-source-file.jpg');
        $target->setFilePath('/tmp/pim-ce/prefix-my-source-file.jpg')->shouldBeCalled();
        $newFile->getMimeType()->willReturn('jpg');
        $target->setMimeType('jpg')->shouldBeCalled();
        $target->resetFile()->shouldBeCalled();

        // update original file name
        $source->getOriginalFilename()->willReturn('my-source-file.jpg');
        $target->setOriginalFilename('my-source-file.jpg')->shouldBeCalled();

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
}
