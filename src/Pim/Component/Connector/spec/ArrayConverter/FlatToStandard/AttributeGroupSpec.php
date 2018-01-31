<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class AttributeGroupSpec extends ObjectBehavior
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
            'code'        => 'sizes',
            'sort_order'  => 1,
            'attributes'  => 'size,main_color',
            'label-en_US' => 'Sizes',
            'label-fr_FR' => 'Tailles'
        ];

        $result = [
            'code'       => 'sizes',
            'sort_order' => 1,
            'attributes' => ['size', 'main_color'],
            'labels'     => [
                'en_US' => 'Sizes',
                'fr_FR' => 'Tailles'
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
