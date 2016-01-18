<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_media(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization($fileInfo, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($fileInfo, 'json')->shouldBe(false);
        $this->supportsNormalization($fileInfo, 'xml')->shouldBe(false);
    }

    function it_normalizes_media(FileInfoInterface $fileInfo)
    {
        $fileInfo->getKey()->willReturn('key/of/the/file.pdf');
        $fileInfo->getOriginalFilename()->willReturn('myfile.pdf');
        $fileInfo->getId()->willReturn(152);

        $this
            ->normalize($fileInfo, 'mongodb_json', [])
            ->shouldReturn([
                'id'               => 152,
                'key'              => 'key/of/the/file.pdf',
                'originalFilename' => 'myfile.pdf',
            ]);
    }
}
