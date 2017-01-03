<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class CurrencySpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'      => 'GLD',
            'activated' => '1',
        ];

        $item = [
            'code'      => 'GLD',
            'activated' => true,
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
