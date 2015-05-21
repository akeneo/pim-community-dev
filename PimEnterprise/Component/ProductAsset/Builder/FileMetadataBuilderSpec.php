<?php

namespace spec\PimEnterprise\Component\ProductAsset\Builder;

use Akeneo\Component\FileMetadata\FileMetadataBagInterface;
use Akeneo\Component\FileMetadata\FileMetadataReader;
use Akeneo\Component\FileMetadata\FileMetadataReaderFactoryInterface;
use PhpSpec\ObjectBehavior;

class FileMetadataBuilderSpec extends ObjectBehavior
{
    function let(FileMetadataReaderFactoryInterface $fileMetadataReaderFactory)
    {
        $this->beConstructedWith($fileMetadataReaderFactory);
    }

    function it_is_a_filemetadata_builder()
    {
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Builder\FileMetadataBuilderInterface');
    }

    function it_builds_a_file_metadata(
        $fileMetadataReaderFactory,
        FileMetadataReader $fileMetaReader,
        FileMetadataBagInterface $metadata
    ) {
        $file = new \SplFileInfo(__FILE__);
        $fileMetadataReaderFactory->create($file)->willReturn($fileMetaReader);
        $fileMetaReader->all($file)->shouldBeCalled();
        $fileMetaReader->getMetadata()->willReturn($metadata);

        $metadata->get('exif.FILE.FileDateTime')->willReturn(123123123);

        $fileMetadata = $this->build($file);

        $fileMetadata->shouldImplement('PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface');
        $fileMetadata->getFileDatetime()->shouldReturn(123123123);
    }
}
