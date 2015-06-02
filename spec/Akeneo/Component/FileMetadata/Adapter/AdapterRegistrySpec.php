<?php

namespace spec\Akeneo\Component\FileMetadata\Adapter;

use Akeneo\Component\FileMetadata\Adapter\AdapterInterface;
use PhpSpec\ObjectBehavior;

class AdapterRegistrySpec extends ObjectBehavior
{
    function let(AdapterInterface $exifAdapter, AdapterInterface $iptcAdapter)
    {
        $exifAdapter->getName()->willReturn('exif');
        $iptcAdapter->getName()->willReturn('iptc');
    }

    function it_registers_adapters($exifAdapter, $iptcAdapter)
    {
        $this->all()->shouldReturn([]);

        $this->add($exifAdapter);
        $this->add($iptcAdapter);

        $this->all()->shouldReturn([
            'exif' => $exifAdapter,
            'iptc' => $iptcAdapter
        ]);
    }

    function it_throws_an_exception_if_an_adapter_is_already_registered($exifAdapter, $iptcAdapter)
    {
        $this->add($exifAdapter);
        $this->add($iptcAdapter);

        $this->shouldThrow('Akeneo\Component\FileMetadata\Exception\AlreadyRegisteredAdapterException')
            ->during('add', [$exifAdapter]);
    }

    function it_returns_an_adapter_by_its_name($exifAdapter, $iptcAdapter)
    {
        $this->add($exifAdapter);
        $this->add($iptcAdapter);

        $this->get('exif')->shouldReturn($exifAdapter);
    }

    function it_throws_an_exception_if_an_adapter_cant_be_found($exifAdapter)
    {
        $this->add($exifAdapter);

        $this->shouldThrow('Akeneo\Component\FileMetadata\Exception\NonRegisteredAdapterException')
            ->during('get', ['iptc']);
    }

    function it_returns_the_existence_of_an_adapter($exifAdapter)
    {
        $this->add($exifAdapter);

        $this->has('exif')->shouldReturn(true);
        $this->has('iptc')->shouldReturn(false);
    }
}
