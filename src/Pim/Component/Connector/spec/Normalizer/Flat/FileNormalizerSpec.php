<?php

namespace spec\Pim\Component\Connector\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Prophecy\Argument;

class FileNormalizerSpec extends ObjectBehavior
{
    function let(FileExporterPathGeneratorInterface $pathGenerator)
    {
        $this->beConstructedWith($pathGenerator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Normalizer\Flat\FileNormalizer');
    }

    function it_normalizes_a_file_for_versioning(FileInfoInterface $fileInfo)
    {
        $fileInfo->getKey()->willReturn('key/of/file.txt');

        $this->normalize(
            $fileInfo,
            null,
            ['versioning' => true, 'field_name' => 'picture']
        )->shouldReturn(['picture' => 'key/of/file.txt']);
    }

    function it_supports_files_and_internal_api(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization($fileInfo, 'csv')->shouldReturn(true);
        $this->supportsNormalization($fileInfo, 'flat')->shouldReturn(true);
        $this->supportsNormalization($fileInfo, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'csv')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'flat')->shouldReturn(false);
    }
}
