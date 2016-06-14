<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class TagSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = ['tags' => 'cat,dog,trex'];
        $item = ['tags' => ['cat', 'dog', 'trex']];

        $this->convert($item)->shouldReturn($expected);
    }
}
