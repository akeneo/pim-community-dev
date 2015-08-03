<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_media(FileInterface $file)
    {
        $this->supportsNormalization($file, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($file, 'json')->shouldBe(false);
        $this->supportsNormalization($file, 'xml')->shouldBe(false);
    }

    function it_normalizes_media(FileInterface $file)
    {
        $file->getKey()->willReturn('key/of/the/file.pdf');
        $file->getOriginalFilename()->willReturn('myfile.pdf');

        $this
            ->normalize($file, 'mongodb_json', [])
            ->shouldReturn(['filename' => 'key/of/the/file.pdf', 'originalFilename' => 'myfile.pdf']);
    }
}
