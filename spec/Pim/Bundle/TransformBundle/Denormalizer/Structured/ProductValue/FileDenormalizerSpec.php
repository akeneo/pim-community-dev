<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\Repository\FileRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileDenormalizerSpec extends ObjectBehavior
{
    function let(FileRepositoryInterface $repository)
    {
        $this->beConstructedWith(['pim_catalog_image', 'pim_catalog_file'], $repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\FileDenormalizer');
    }

    function it_denormalizes_an_empty_value()
    {
        $this->denormalize('', Argument::cetera())->shouldReturn(null);
        $this->denormalize(null, Argument::cetera())->shouldReturn(null);
    }

    function it_denormalizes_an_existing_file($repository, FileInterface $file)
    {
        $repository->findOneByIdentifier('key/of/file.txt')->willReturn($file);

        $this->denormalize(['filePath' => 'key/of/file.txt'], Argument::cetera())->shouldReturn($file);
    }

    function it_supports_denormalization_of_files_and_images_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_image', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_file', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_image', 'csv')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_file', 'csv')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
    }
}
