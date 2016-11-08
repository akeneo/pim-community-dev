<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class ChannelConfigurationSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'mobile' => [
                'configuration' => [
                    'width' => 500,
                    'scale' => 2
                ]
            ]
        ];

        $item = [
            'channel' => 'mobile',
            'configuration' => [
                'width' => 500,
                'scale' => 2
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
