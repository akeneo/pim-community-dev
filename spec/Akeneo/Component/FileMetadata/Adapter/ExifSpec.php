<?php

namespace spec\Akeneo\Component\FileMetadata\Adapter;

use PhpSpec\ObjectBehavior;

class ExifSpec extends ObjectBehavior
{
    function it_is_an_adapter()
    {
        $this->shouldHaveType('Akeneo\Component\FileMetadata\Adapter\AdapterInterface');
    }

    function it_returns_its_name()
    {
        $this->getName()->shouldReturn('exif');
    }

    function it_returns_supported_mime_types()
    {
        $this->getSupportedMimeTypes()->shouldReturn([
            'image/jpeg', 'image/tiff'
        ]);
    }

    function it_supports_some_mime_types()
    {
        $this->isMimeTypeSupported('image/tiff')->shouldReturn(true);
        $this->isMimeTypeSupported('image/jpeg')->shouldReturn(true);
        $this->isMimeTypeSupported('image/png')->shouldReturn(false);
    }
}
