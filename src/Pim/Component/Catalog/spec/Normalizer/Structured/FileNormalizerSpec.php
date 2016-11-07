<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;

class FileNormalizerSpec extends ObjectBehavior
{
    function it_normalizes_a_file_for_versioning(FileInfoInterface $fileInfo)
    {
        $fileInfo->getKey()->willReturn('key/of/file.txt');
        $fileInfo->getOriginalFilename()->willReturn('the file.txt');
        $fileInfo->getHash()->willReturn('98s7qf987a6f4sdqf');

        $this->normalize($fileInfo)->shouldReturn(
            [
                'filePath' => 'key/of/file.txt',
                'originalFilename' => 'the file.txt',
                'hash' => '98s7qf987a6f4sdqf'
            ]
        );
    }

    function it_supports_files_and_internal_api(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization($fileInfo, 'json')->shouldReturn(true);
        $this->supportsNormalization($fileInfo, 'xml')->shouldReturn(true);
        $this->supportsNormalization($fileInfo, 'csv')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'xml')->shouldReturn(false);
    }
}
