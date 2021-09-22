<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\StandardToFlat\SelectOptionDetails;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;

class SelectOptionDetailsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
        $this->shouldHaveType(SelectOptionDetails::class);
    }

    function it_converts_a_standard_select_option_details_to_flat_format()
    {
        $this->convert(
            [
                'attribute' => 'foo',
                'column' => 'bar',
                'code' => 'baz',
                'labels' => [
                    'en_US' => 'An option',
                    'fr_FR' => 'Une option',
                ],
            ]
        )->shouldReturn(
            [
                'attribute' => 'foo',
                'column' => 'bar',
                'code' => 'baz',
                'label-en_US' => 'An option',
                'label-fr_FR' => 'Une option',
            ]
        );
    }

    function it_filters_non_existing_properties()
    {
        $this->convert(
            [
                'foo' => 'bar',
                'attribute' => 'test',
                'unknown' => 'test',
                'labels' => ['en_US' => 'toto'],
            ]
        )->shouldReturn(
            [
                'attribute' => 'test',
                'label-en_US' => 'toto',
            ]
        );
    }
}
