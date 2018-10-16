<?php

namespace Specification\Akeneo\Asset\Component\Builder;

use Akeneo\Asset\Component\Builder\MetadataBuilderInterface;
use Akeneo\Asset\Component\Model\FileMetadataInterface;
use PhpSpec\ObjectBehavior;

class FileMetadataBuilderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_is_a_metadata_builder()
    {
        $this->shouldImplement(MetadataBuilderInterface::class);
    }

    function it_builds_a_file_metadata(\SplFileInfo $file)
    {
        $timestamp = 123456789;
        $file->getMTime()->willReturn($timestamp);
        $expectedDateTime = new \DateTime(sprintf('@%s', $timestamp));

        $fileMetadata = $this->build($file);

        $fileMetadata->shouldImplement(FileMetadataInterface::class);
        $fileMetadata->getModificationDatetime()->getTimestamp()->shouldReturn($expectedDateTime->getTimestamp());
    }
}
