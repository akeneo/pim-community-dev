<?php

namespace spec\Akeneo\Pim\Permission\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class LocaleAccessesSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface'
        );
    }

    function it_converts_an_item_to_standard_format()
    {

        $item = [
            'locale'        => 'en_US',
            'view_products' => 'IT support,Manager',
            'edit_products' => 'IT support',
        ];

        $result = [
            [
                'locale'        => 'en_US',
                'user_group'    => 'IT support',
                'view_products' => true,
                'edit_products' => true,
            ], [
                'locale'        => 'en_US',
                'user_group'    => 'Manager',
                'view_products' => true,
                'edit_products' => false,
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
