<?php

namespace spec\Akeneo\Asset\Component\Builder;

use PhpSpec\ObjectBehavior;

class FileMetadataBuilderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_is_a_metadata_builder()
    {
        $this->shouldImplement('Akeneo\Asset\Component\Builder\MetadataBuilderInterface');
    }

    function it_builds_a_file_metadata(\SplFileInfo $file)
    {
        $timestamp = 123456789;
        $file->getMTime()->willReturn($timestamp);
        $expectedDateTime = new \DateTime(sprintf('@%s', $timestamp));

        $fileMetadata = $this->build($file);

        $fileMetadata->shouldImplement('Akeneo\Asset\Component\Model\FileMetadataInterface');
        $fileMetadata->getModificationDatetime()->getTimestamp()->shouldReturn($expectedDateTime->getTimestamp());
    }
}
