<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class GroupTypeSpec extends ObjectBehavior
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
            'code'        => 'VARIANT',
            'label-en_US' => 'variant',
            'label-fr_FR' => 'variantes',
        ];

        $result = [
            'code'        => 'VARIANT',
            'label'       => [
                'en_US' => 'variant',
                'fr_FR' => 'variantes',
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
