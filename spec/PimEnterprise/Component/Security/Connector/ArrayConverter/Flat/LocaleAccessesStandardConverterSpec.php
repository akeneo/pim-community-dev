<?php

namespace spec\PimEnterprise\Component\Security\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;

class LocaleAccessesStandardConverterSpec extends ObjectBehavior
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
            'locale'        => 'en_US',
            'view_products' => 'IT support,Manager',
            'edit_products' => 'IT support',
        ];

        $result = [
            [
                'locale'        => 'en_US',
                'userGroup'     => 'IT support',
                'view_products' => true,
                'edit_products' => true,
            ], [
                'locale'        => 'en_US',
                'userGroup'     => 'Manager',
                'view_products' => true,
                'edit_products' => false,
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
