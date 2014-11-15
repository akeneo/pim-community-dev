<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
        $media->setOriginalFilename( 'my-new-file.jpg')->shouldBeCalled();
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
        $media->setOriginalFilename( 'my-new-file.jpg')->shouldBeCalled();
        $media->setFilename('prefix-my-new-file.jpg')->shouldBeCalled();
        $media->setFilePath('/tmp/pim-ce/my-new-file.jpg')->shouldBeCalled();
        $newFile->getMimeType()->willReturn('jpg');
        $media->setMimeType('jpg')->shouldBeCalled();
        $media->resetFile()->shouldBeCalled();

        $this->handle($media, 'prefix');
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
