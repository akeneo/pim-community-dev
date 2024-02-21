<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\FileNormalizer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FileNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FileNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_format_and_file_info_objects(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization($fileInfo, 'standard')->shouldReturn(true);
    }

    function it_does_not_supports_other_formats_or_objects(
        FileInfoInterface $fileInfo
    ) {
        $this->supportsNormalization($fileInfo, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_file_info(FileInfoInterface $fileInfo)
    {
        $standardFile = [
            'code'              => 'f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt',
            'original_filename' => 'file a',
            'mime_type'         => 'plain/text',
            'size'              => 2355,
            'extension'         => 'txt'
        ];

        $fileInfo->getKey()->willReturn('f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt');
        $fileInfo->getOriginalFilename()->willReturn('file a');
        $fileInfo->getMimeType()->willReturn('plain/text');
        $fileInfo->getSize()->willReturn(2355);
        $fileInfo->getExtension()->willReturn('txt');

        $this->normalize($fileInfo, 'standard')->shouldReturn($standardFile);
    }
}
