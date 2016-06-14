<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class ChannelConfigurationSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'          => 'mycode',
            'configuration' => [
                'ecommerce' => ['scale' => ['ratio' => 0.5]],
                'tablet'    => ['scale' => ['ratio' => 0.25]],
            ]
        ];

        $item = [
            'channel'       => 'mycode',
            'configuration' => [
                'ecommerce' => ['scale' => ['ratio' => 0.5]],
                'tablet'    => ['scale' => ['ratio' => 0.25]],
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
