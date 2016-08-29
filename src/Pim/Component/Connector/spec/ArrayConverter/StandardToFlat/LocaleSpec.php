<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class LocaleSpec extends ObjectBehavior
{
    function it_is_an_array_converter()
    {
        $this->shouldImplement('Pim\Component\Connector\ArrayConverter\ArrayConverterInterface');
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
