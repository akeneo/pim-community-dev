<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;

class MetricConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, []);
    }

    function it_converts_metric_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('overall_frequency', null, 'mobile')
            ->willReturn('overall_frequency-mobile');

        $expected = [
            'overall_frequency-mobile'      => '229',
            'overall_frequency-mobile-unit' => 'HERTZ',
        ];

        $data = [
            [
                'locale' => null,
                'scope'  => 'mobile',
                'data'   => [
                    'data' => '229',
                    'unit' => 'HERTZ',
                ],
            ]
        ];

        $this->convert('overall_frequency', $data)->shouldReturn($expected);
    }
}
