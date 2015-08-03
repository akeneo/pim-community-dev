<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer');
    }

    function it_normalizes_a_file(FileInterface $file)
    {
        $file->getKey()->willReturn('key/of/file.txt');
        $file->getOriginalFilename()->willReturn('original file name.txt');

        $this->normalize($file, Argument::cetera())->shouldReturn(
            [
                'filePath' => 'key/of/file.txt',
                'originalFilename' => 'original file name.txt',
            ]
        );
    }

    function it_supports_files_and_internal_api(FileInterface $file)
    {
        $this->supportsNormalization($file, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($file, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'internal_api')->shouldReturn(false);
    }
}
