<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Exception\ArrayConversionException;

class ChannelConfigurationStandardConverterSpec extends ObjectBehavior
{
    function it_converts()
    {
        $jsonConfiguration = '{"ecommerce":{"scale":{"ratio":0.5}},"tablet":{"scale":{"ratio":0.25}},"mobile":{"scale"'
            .':{"width":200},"colorspace":{"colorspace":"gray"}},"print":{"resize":{"width":400,"height":200}}}';
        $fields = [
            'channel'       => 'mycode',
            'configuration' => $jsonConfiguration
        ];

        $convertedConfiguration = [
            'ecommerce' => ['scale' => ['ratio' => 0.5]],
            'tablet'    => ['scale' => ['ratio' => 0.25]],
            'mobile'    => [
                'scale'      => ['width'      => 200],
                'colorspace' => ['colorspace' => 'gray'],
            ],
            'print'     => ['resize' => ['width' => 400, 'height' => 200]],
        ];

        $this->convert($fields)->shouldReturn([
            'channel'       => 'mycode',
            'configuration' => $convertedConfiguration
        ]);
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "channel" is expected, provided fields are "not_a_code"'))->during(
            'convert',
            [['not_a_code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_code_is_empty()
    {
        $this->shouldThrow(new \LogicException('Field "channel" must be filled'))->during(
            'convert',
            [['channel' => '']]
        );
    }
}
