<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Structured;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileNormalizerSpec extends ObjectBehavior
{
    function it_normalizes_a_file_for_versioning(FileInterface $file)
    {
        $file->getKey()->willReturn('key/of/file.txt');
        $file->getOriginalFilename()->willReturn('the file.txt');
        $file->getHash()->willReturn('98s7qf987a6f4sdqf');

        $this->normalize($file)->shouldReturn(
            [
                'filePath' => 'key/of/file.txt',
                'originalFilename' => 'the file.txt',
                'hash' => '98s7qf987a6f4sdqf'
            ]
        );
    }

    function it_supports_files_and_internal_api(FileInterface $file)
    {
        $this->supportsNormalization($file, 'json')->shouldReturn(true);
        $this->supportsNormalization($file, 'xml')->shouldReturn(true);
        $this->supportsNormalization($file, 'csv')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'xml')->shouldReturn(false);
    }
}
