<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;

class MediaDenormalizerSpec extends ObjectBehavior
{
    function let(MediaManager $manager)
    {
        $this->beConstructedWith(
            ['pim_catalog_image', 'pim_catalog_file'],
            $manager
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\MediaDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_file_and_image_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_file', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_image', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_file', 'csv')->shouldReturn(false);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_file', 'json')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_image', 'json')->shouldReturn(null);
        $this->denormalize([], 'pim_catalog_image', 'json')->shouldReturn(null);
    }

    function it_denormalizes_data_into_media($manager, ProductMediaInterface $media)
    {
        $manager
            ->createFromFilePath('foo/bar/image.jpg')
            ->shouldBeCalled()
            ->willReturn($media);

        $media->setOriginalFilename('Nice picture')->shouldBeCalled();

        $this
            ->denormalize(
                [
                    'filePath' => 'foo/bar/image.jpg',
                    'originalFilename' => 'Nice picture'
                ],
                'pim_catalog_image',
                'json'
            )
            ->shouldReturn($media);
    }
}
