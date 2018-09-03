<?php

namespace spec\Akeneo\Tool\Component\Api\Converter;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Converter\MeasureFamilyConverter;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Prophecy\Argument;

class MeasureFamilyConverterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MeasureFamilyConverter::class);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_a_measure_family()
    {
        $item = [
            'family_code' => 'area',
            'units' => [
                'standard' => 'SQUARE_METER',
                'units' => [
                    'SQUARE_MILLIMETER' => [
                        'convert' => [['mul'=> '0.000001']],
                        'symbol' => 'cm²',
                    ],
                ]
            ]
        ];

        $convertedItem = [
            "code" => $item['family_code'],
            "standard" => $item['units']['standard'],
            "units" => [
                [
                    'code' => 'SQUARE_MILLIMETER',
                    'convert' => ['mul'=> '0.000001'],
                    'symbol' => 'cm²',
                ],
            ]
        ];

        $this->convert($item)->shouldReturn($convertedItem);
    }
}
