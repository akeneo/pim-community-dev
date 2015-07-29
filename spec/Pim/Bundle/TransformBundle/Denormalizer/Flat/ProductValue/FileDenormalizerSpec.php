<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Akeneo\Component\FileStorage\Repository\FileRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileDenormalizerSpec extends ObjectBehavior
{
    function let(FileRepositoryInterface $repository, RawFileStorerInterface $storer)
    {
        $this->beConstructedWith(['pim_catalog_image'], $repository, $storer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue\FileDenormalizer');
    }

    function it_denormalizes_by_retreving_an_existing_file($repository, FileInterface $file)
    {
        $repository->findOneByIdentifier('1/2/3/123_file.txt')->willReturn($file);

        $this->denormalize('1/2/3/123_file.txt', 'File')->shouldReturn($file);
    }

    function it_denormalizes_by_storing_a_new_file($storer, FileInterface $file)
    {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');

        $storer->store(Argument::any(), 'storage')->willReturn($file);
        $this->denormalize($pathname, 'File')->shouldReturn($file);

        unlink($pathname);
    }


}
