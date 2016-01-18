<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FileStorage;
use Prophecy\Argument;

class FileDenormalizerSpec extends ObjectBehavior
{
    function let(FileInfoRepositoryInterface $repository, FileStorerInterface $storer)
    {
        $this->beConstructedWith(['pim_catalog_image'], $repository, $storer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue\FileDenormalizer');
    }

    function it_denormalizes_by_retreving_an_existing_file($repository, FileInfoInterface $fileInfo)
    {
        $repository->findOneByIdentifier('1/2/3/123_file.txt')->willReturn($fileInfo);

        $this->denormalize('1/2/3/123_file.txt', 'File')->shouldReturn($fileInfo);
    }

    function it_denormalizes_by_storing_a_new_file($storer, FileInfoInterface $fileInfo)
    {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');

        $storer->store(Argument::any(), FileStorage::CATALOG_STORAGE_ALIAS)->willReturn($fileInfo);
        $this->denormalize($pathname, 'File')->shouldReturn($fileInfo);

        unlink($pathname);
    }


}
