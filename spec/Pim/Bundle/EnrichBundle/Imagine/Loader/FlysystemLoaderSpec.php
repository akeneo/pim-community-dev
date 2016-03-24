<?php

namespace spec\Pim\Bundle\EnrichBundle\Imagine\Loader;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FileStorage;

class FlysystemLoaderSpec extends ObjectBehavior
{
    function let(MountManager $mountManager, FilesystemInterface $filesystem)
    {
        $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)->willReturn($filesystem);

        $this->beConstructedWith($mountManager, [FileStorage::CATALOG_STORAGE_ALIAS]);
    }

    function it_is_a_loader()
    {
        $this->shouldHaveType('\Liip\ImagineBundle\Binary\Loader\LoaderInterface');
    }

    function it_finds_a_file_with_a_given_path($filesystem)
    {
        $filepath = '2/f/a/4/2fa4afe5465afe5655age_flower.png';

        $filesystem->has($filepath)->willReturn(true);
        $filesystem->read($filepath)->willReturn('IMAGE CONTENT');
        $filesystem->getMimetype($filepath)->willReturn('image/png');

        $binary = $this->find($filepath);

        $binary->getContent()->shouldReturn('IMAGE CONTENT');
        $binary->getMimeType()->shouldReturn('image/png');
    }
}
