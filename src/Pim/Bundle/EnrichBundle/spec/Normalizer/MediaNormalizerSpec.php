<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;


use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer;
use Pim\Component\Catalog\Value\MediaValue;

class MediaNormalizerSpec extends ObjectBehavior
{
    function let(FileNormalizer $fileNormalizer)
    {
        $this->beConstructedWith($fileNormalizer);
    }

    function it_should_be_a_media_normalizer()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Normalizer\MediaNormalizer');
    }

    function it_supports_media(MediaValue $media)
    {
        $this->supportsNormalization($media, 'internal_api')->shouldReturn(true);
    }

    function it_does_not_support_anything_else(\stdClass $anything)
    {
        $this->supportsNormalization($anything, 'internal_api')->shouldReturn(false);
    }

    function it_normalizes_media($fileNormalizer, MediaValue $media, FileInfoInterface $file)
    {
        $media->getData()->willReturn($file);

        $fileNormalizer->normalize($file, 'internal_api', [])->willReturn([
            'filePath'         => 'fileKey',
            'originalFilename' => 'fileOriginalFilename',
        ]);

        $this->normalize($media, 'internal_api', [])->shouldReturn([
            'filePath'         => 'fileKey',
            'originalFilename' => 'fileOriginalFilename',
        ]);
    }
}
