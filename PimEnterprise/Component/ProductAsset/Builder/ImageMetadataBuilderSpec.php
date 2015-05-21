<?php

namespace spec\PimEnterprise\Component\ProductAsset\Builder;

use Akeneo\Component\FileMetadata\FileMetadataBagInterface;
use Akeneo\Component\FileMetadata\FileMetadataReader;
use Akeneo\Component\FileMetadata\FileMetadataReaderFactoryInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Builder\FileMetadataBuilderInterface;
use PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface;

class ImageMetadataBuilderSpec extends ObjectBehavior
{
    function let(
        FileMetadataReaderFactoryInterface $fileMetadataReaderFactory,
        FileMetadataBuilderInterface $fileMetaBuidler
    ) {
        $this->beConstructedWith($fileMetadataReaderFactory, $fileMetaBuidler);
    }

    function it_is_a_filemetadata_builder()
    {
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Builder\ImageMetadataBuilderInterface');
    }

    function it_builds_an_image_metadata(
        $fileMetadataReaderFactory,
        $fileMetaBuidler,
        FileMetadataReader $fileMetaReader,
        FileMetadataBagInterface $metadata,
        FileMetadataInterface $fileMetadata
    ) {
        $file = new \SplFileInfo(__FILE__);
        $fileMetadataReaderFactory->create($file)->willReturn($fileMetaReader);
        $fileMetaReader->all($file)->shouldBeCalled();
        $fileMetaReader->getMetadata()->willReturn($metadata);

        $fileMetaBuidler->build($file)->willReturn($fileMetadata);
        $fileMetadata->getFileDatetime()->willReturn(123123123);

        $metadata->get('exif.FILE.FileDateTime')->willReturn(123123123);
        $metadata->get('exif.EXIF.DateTimeOriginal')->willReturn('2012-05-08 12:20:33');
        $metadata->get('exif.IFD0.Make')->willReturn('Canon');
        $metadata->get('exif.IFD0.Model')->willReturn('LTH 5660r');
        $metadata->get('exif.COMPUTED.Width')->willReturn('1920');
        $metadata->get('exif.COMPUTED.Height')->willReturn('700');
        $metadata->get('exif.IFD0.Orientation')->willReturn(1);
        $metadata->get('exif.IFD0.Copyright')->willReturn('Creative Commons');
        $metadata->get('exif.IFD0.Keywords')->willReturn('fisherman');
        $metadata->get('exif.IFD0.Title')->willReturn('Fishing boat');
        $metadata->get('exif.IFD0.Subject')->willReturn('A beatiful boat taken in Iceland');
        $metadata->get('iptc.Keywords', [])->willReturn(['boat', 'fish']);
        $metadata->get('iptc.LocationName')->willReturn('Iceland');
        $metadata->get('iptc.City')->willReturn('Reykjavik');
        $metadata->get('exif.IFD0.XResolution')->willReturn('300/1');
        $metadata->get('exif.IFD0.ResolutionUnit')->willReturn(2);

        $imageMetadata = $this->build($file);

        $imageMetadata->shouldImplement('PimEnterprise\Component\ProductAsset\Model\ImageMetadataInterface');
        $imageMetadata->getFileDatetime()->shouldReturn(123123123);
        $imageMetadata->getExifDateTimeOriginal()->shouldReturn('2012-05-08 12:20:33');
        $imageMetadata->getExifCameraMake()->shouldReturn('Canon');
        $imageMetadata->getExifCameraModel()->shouldReturn('LTH 5660r');
        $imageMetadata->getExifSizeWidth()->shouldReturn('1920');
        $imageMetadata->getExifSizeHeight()->shouldReturn('700');
        $imageMetadata->getExifOrientation()->shouldReturn(1);
        $imageMetadata->getExifCopyright()->shouldReturn('Creative Commons');
        $imageMetadata->getExifKeywords()->shouldReturn('fisherman');
        $imageMetadata->getExifTitle()->shouldReturn('Fishing boat');
        $imageMetadata->getExifDescription()->shouldReturn('A beatiful boat taken in Iceland');
        $imageMetadata->getIptcKeywords()->shouldReturn('boat,fish');
        $imageMetadata->getIptcLocationCountry()->shouldReturn('Iceland');
        $imageMetadata->getIptcLocationCity()->shouldReturn('Reykjavik');
        $imageMetadata->getExifResolution()->shouldReturn('300 DPI');
    }
}
