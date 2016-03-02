<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;

class ChannelStandardConverterSpec extends ObjectBehavior
{
    function let(FieldsRequirementValidator $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface'
        );
    }

    function it_converts_an_item_to_standard_format()
    {
        $item = [
            'code'       => 'ecommerce',
            'label'      => 'Ecommerce',
            'locales'    => 'en_US,fr_FR',
            'currencies' => 'EUR,USD',
            'tree'       => 'master_catalog',
            'color'      => 'orange'
        ];

        $result = [
            'code'       => 'ecommerce',
            'label'      => 'Ecommerce',
            'locales'    => ['en_US', 'fr_FR'],
            'currencies' => ['EUR', 'USD'],
            'tree'       => 'master_catalog',
            'color'      => 'orange'
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
