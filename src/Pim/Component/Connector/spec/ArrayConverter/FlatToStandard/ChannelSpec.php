<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class ChannelSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\ArrayConverterInterface'
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
