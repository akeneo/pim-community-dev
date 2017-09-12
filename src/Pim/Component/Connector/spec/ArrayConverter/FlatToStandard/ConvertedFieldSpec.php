<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConvertedFieldSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('family', 'family');

        $this->shouldHaveType(ConvertedField::class);
    }

    function it_appends_association_to_converted_item()
    {
        $this->beConstructedWith('associations', ['X_SELL' => ['groups' => ['value', 'test']]]);

        $this->appendTo([
            'associations' => ['UP_SELL' => ['groups' => ['value2', 'test2']]]
        ])->shouldReturn([
            'associations' => [
                'UP_SELL' => ['groups' => ['value2', 'test2']],
                'X_SELL' => ['groups' => ['value', 'test']],
            ]
        ]);
    }

    function it_appends_simple_field_to_converted_item()
    {
        $this->beConstructedWith('family', 'family');

        $this->appendTo([
            'associations' => ['UP_SELL' => ['groups' => ['value2', 'test2']]]
        ])->shouldReturn([
            'associations' => [
                'UP_SELL' => ['groups' => ['value2', 'test2']],
            ],
            'family' => 'family'
        ]);
    }
}
