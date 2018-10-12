<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class GroupTypeSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            ArrayConverterInterface::class
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
