<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
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

    /*
    function it_handles_existing_product_media_upload($filesystem, ProductMediaInterface $media, File $newFile)
    {
    }

    function it_handles_product_media_deletion()
    {

    }

    function it_duplicates_product_media()
    {
    }

    function it_copies_product_media()
    {
    }

    function it_provides_export_path()
    {
    }

    function it_generates_filename_prefix()
    {
    }*/
}
