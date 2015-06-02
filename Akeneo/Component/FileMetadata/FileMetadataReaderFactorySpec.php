<?php

namespace spec\Akeneo\Component\FileMetadata;

use Akeneo\Component\FileMetadata\Adapter\AdapterInterface;
use Akeneo\Component\FileMetadata\Adapter\AdapterRegistry;
use PhpSpec\ObjectBehavior;

class FileMetadataReaderFactorySpec extends ObjectBehavior
{
    function let(
        AdapterRegistry $adapterRegistry,
        AdapterInterface $exifAdapter,
        AdapterInterface $iptcAdapter
    ) {
        $adapterRegistry->all()->willReturn([$exifAdapter, $iptcAdapter]);

        $this->beConstructedWith($adapterRegistry);
    }

    function it_is_a_factory()
    {
        $this->shouldHaveType('Akeneo\Component\FileMetadata\FileMetadataReaderFactoryInterface');
    }

    function it_creates_a_file_metadata_reader_for_the_given_file(
        $exifAdapter,
        $iptcAdapter
    ) {
        $file = new \SplFileInfo(__FILE__);

        $exifAdapter->isMimeTypeSupported('text/x-php')->willReturn(true);
        $iptcAdapter->isMimeTypeSupported('text/x-php')->willReturn(false);

        $exifAdapter->getName()->willReturn('exif');
        $exifAdapter->all($file)->willReturn(['exif' => []])->shouldBeCalled();
        $iptcAdapter->all($file)->shouldNotBeCalled();

        $reader = $this->create($file);

        $reader->shouldHaveType('Akeneo\Component\FileMetadata\FileMetadataReaderInterface');
        $reader->all($file);
    }
}
