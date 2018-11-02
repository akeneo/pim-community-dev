<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\FileNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FileNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileNormalizer::class);
    }

    function it_supports_file_infos(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization($fileInfo, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($fileInfo, 'storage')->shouldReturn(true);
    }

    function it_normalizes_file_infos($stdNormalizer, FileInfoInterface $fileInfo)
    {
        $stdNormalizer->normalize($fileInfo, 'storage', ['context'])->willReturn('file_info');

        $this->normalize($fileInfo, 'storage', ['context'])->shouldReturn('file_info');
    }
}
