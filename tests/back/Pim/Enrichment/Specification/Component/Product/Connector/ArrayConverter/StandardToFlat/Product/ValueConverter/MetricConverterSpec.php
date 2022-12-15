<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;

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
            'overall_frequency-mobile-symbol' => 'Hz',
        ];

        $data = [
            [
                'locale' => null,
                'scope'  => 'mobile',
                'data'   => [
                    'amount' => '229',
                    'unit'   => 'HERTZ',
                    'symbol' => 'Hz',
                ],
            ]
        ];

        $this->convert('overall_frequency', $data)->shouldReturn($expected);
    }

    function it_converts_empty_metric_product_value_from_standard_to_flat_format($columnsResolver)
    {
        $columnsResolver->resolveFlatAttributeName('overall_frequency', null, 'mobile')
            ->willReturn('overall_frequency-mobile');

        $expected = [
            'overall_frequency-mobile'      => null,
            'overall_frequency-mobile-unit' => null,
            'overall_frequency-mobile-symbol' => null,
        ];

        $data = [
            [
                'locale' => null,
                'scope'  => 'mobile',
                'data'   => [
                    'amount' => null,
                    'unit'   => 'HERTZ',
                    'symbol'   => 'Hz',
                ],
            ]
        ];

        $this->convert('overall_frequency', $data)->shouldReturn($expected);
    }
}
