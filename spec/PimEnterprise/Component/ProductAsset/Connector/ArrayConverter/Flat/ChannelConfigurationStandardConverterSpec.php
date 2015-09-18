<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Exception\ArrayConversionException;

class ChannelConfigurationStandardConverterSpec extends ObjectBehavior
{
    function it_converts()
    {
        $fields = [
            'code'          => 'mycode',
            'configuration' => [
                'ecommerce' => ['scale' => ['ratio' => 0.5]],
                'tablet'    => ['scale' => ['ratio' => 0.25]],
                'mobile'    => ['scale' => ['width'=> 200], 'colorspace' => ['colorspace' => 'gray']],
                'print'     => ['resize' => ['width' => 400, 'height' => 200]],
            ]
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
        $this->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))->during(
            'convert',
            [['not_a_code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_code_is_empty()
    {
        $this->shouldThrow(new \LogicException('Field "code" must be filled'))->during(
            'convert',
            [['code' => '']]
        );
    }
}
