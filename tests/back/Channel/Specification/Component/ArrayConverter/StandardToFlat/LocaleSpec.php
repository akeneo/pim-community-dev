<?php

namespace Specification\Akeneo\Channel\Component\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;

class LocaleSpec extends ObjectBehavior
{
    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'      => 'en',
            'activated' => "1",
        ];

        $item = [
            'code'      => 'en',
            'activated' => true,
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
