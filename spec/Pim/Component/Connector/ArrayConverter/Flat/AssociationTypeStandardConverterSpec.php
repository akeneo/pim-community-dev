<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;

class AssociationTypeStandardConverterSpec extends ObjectBehavior
{
    function it_converts()
    {
        $fields = [
            'code'        => 'mycode',
            'label-fr_FR' => 'Vente croisée',
            'label-en_US' => 'Cross sell',
        ];

        $this->convert($fields)->shouldReturn(
            [
                'labels' => [
                    'fr_FR' => 'Vente croisée',
                    'en_US' => 'Cross sell',
                ],
                'code'   => 'mycode',
            ]
        );
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'))->during(
            'convert',
            [['not_a_code' => '']]
        );
    }

    function it_throws_an_exception_if_required_field_code_is_empty()
    {
        $this->shouldThrow(new \LogicException('Field "code" must be filled'))->during(
            'convert',
            [['code' => '']]
        );
    }
}
