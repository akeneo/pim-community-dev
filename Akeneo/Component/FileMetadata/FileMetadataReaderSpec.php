<?php

namespace spec\Akeneo\Component\FileMetadata;

use Akeneo\Component\FileMetadata\Adapter\AdapterInterface;
use PhpSpec\ObjectBehavior;

class FileMetadataReaderSpec extends ObjectBehavior
{
    function let(AdapterInterface $exifAdapter, AdapterInterface $iptcAdapter)
    {
        $this->beConstructedWith([$exifAdapter, $iptcAdapter]);
    }

    function it_is_a_reader()
    {
        $this->shouldHaveType('Akeneo\Component\FileMetadata\FileMetadataReaderInterface');
    }

    function it_returns_all_metadata_from_its_adapters($exifAdapter, $iptcAdapter, \SplFileInfo $file)
    {
        $exifAdapter->all($file)->willReturn([
            'author'   => 'Georges',
            'exposure' => '55'
        ]);

        $iptcAdapter->all($file)->willReturn([
            'colorscale' => 'RGB'
        ]);

        $this->all($file)->shouldReturn([
            'author'   => 'Georges',
            'exposure' => '55',
            'colorscale' => 'RGB'
        ]);
    }
}
