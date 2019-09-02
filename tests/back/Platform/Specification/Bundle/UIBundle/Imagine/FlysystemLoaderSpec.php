<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Imagine;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;

class FlysystemLoaderSpec extends ObjectBehavior
{
    function let(FilesystemProvider $filesystemProvider, FilesystemInterface $filesystem, FileInfoRepositoryInterface $fileInfoRepository)
    {
        $filesystemProvider->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)->willReturn($filesystem);

        $this->beConstructedWith($filesystemProvider, [FileStorage::CATALOG_STORAGE_ALIAS], $fileInfoRepository);
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

    function it_sets_the_mimetype_of_a_binary_file($filesystem, $fileInfoRepository, FileInfoInterface $fileInfo)
    {
        $filepath = '2/f/a/4/2fa4afe5465afe5655age_flower';

        $filesystem->has($filepath)->willReturn(true);
        $filesystem->read($filepath)->willReturn('IMAGE CONTENT');
        $filesystem->getMimetype($filepath)->willReturn('application/octet-stream');

        $fileInfo->getMimeType()->willReturn('image/png');
        $fileInfoRepository->findOneByIdentifier($filepath)->willReturn($fileInfo);

        $binary = $this->find($filepath);

        $binary->getContent()->shouldReturn('IMAGE CONTENT');
        $binary->getMimeType()->shouldReturn('image/png');
    }
}
