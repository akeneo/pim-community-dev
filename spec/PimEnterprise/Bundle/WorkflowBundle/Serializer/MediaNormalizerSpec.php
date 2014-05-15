<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\Media;

class MediaNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_normalization_of_media_in_the_proposal_format(Media $media)
    {
        $this->supportsNormalization($media, 'proposal')->shouldBe(true);
    }

    function it_normalizes_media_object(Media $media)
    {
        $media->getFilename()->willReturn('foo.jpg');
        $media->getFilePath()->willReturn('/tmp');
        $media->getOriginalFilename()->willReturn('bar.jpg');
        $media->getMimeType()->willReturn('image/jpeg');

        $this->normalize($media, 'proposal')->shouldReturn(
            [
                'filename' => 'foo.jpg',
                'filePath' => '/tmp',
                'originalFilename' => 'bar.jpg',
                'mimeType' => 'image/jpeg',
            ]
        );
    }

    function it_supports_denormalization_of_file_attribute_in_the_proposal_format()
    {
        $this->supportsDenormalization([], 'pim_catalog_file', 'proposal')->shouldBe(true);
    }

    function it_supports_denormalization_of_image_attribute_in_the_proposal_format()
    {
        $this->supportsDenormalization([], 'pim_catalog_image', 'proposal')->shouldBe(true);
    }

    function it_denormalizes_media_data_into_the_context_media(Media $media)
    {
        $media->setFilename('foo.jpg')->willReturn($media);
        $media->setFilePath('/tmp')->willReturn($media);
        $media->setOriginalFilename('bar.jpg')->willreturn($media);
        $media->setMimeType('image/jpeg')->willReturn($media);

        $this->denormalize(
            [
                'filename' => 'foo.jpg',
                'filePath' => '/tmp',
                'originalFilename' => 'bar.jpg',
                'mimeType' => 'image/jpeg',
            ],
            'pim_catalog_image',
            'proposal',
            ['instance' => $media]
        )->shouldReturn($media);
    }
}
