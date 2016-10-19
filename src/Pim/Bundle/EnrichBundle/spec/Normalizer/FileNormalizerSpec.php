<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer');
    }

    function it_normalizes_a_file(FileInfoInterface $fileInfo)
    {
        $fileInfo->getKey()->willReturn('key/of/file.txt');
        $fileInfo->getOriginalFilename()->willReturn('original file name.txt');

        $this->normalize($fileInfo, Argument::cetera())->shouldReturn(
            [
                'filePath' => 'key/of/file.txt',
                'originalFilename' => 'original file name.txt',
            ]
        );
    }

    function it_supports_files_and_internal_api(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization($fileInfo, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($fileInfo, 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'internal_api')->shouldReturn(false);
    }
}
