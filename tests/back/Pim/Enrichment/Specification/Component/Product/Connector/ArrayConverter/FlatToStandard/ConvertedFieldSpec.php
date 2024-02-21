<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use PhpSpec\ObjectBehavior;

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

    function it_appends_association_with_number_to_converted_item()
    {
        $this->beConstructedWith('associations', [1234 => ['groups' => ['value', 'test']]]);

        $this->appendTo([
            'associations' => ['UP_SELL' => ['groups' => ['value2', 'test2']]]
        ])->shouldReturn([
            'associations' => [
                'UP_SELL' => ['groups' => ['value2', 'test2']],
                1234 => ['groups' => ['value', 'test']],
            ]
        ]);
    }

    function it_appends_associations_with_numbers_to_converted_item()
    {
        $this->beConstructedWith('associations', [1234 => ['groups' => ['value', 'test']]]);

        $this->appendTo([
            'associations' => [1234 => ['products' => ['value2', 'test2']]]
        ])->shouldReturn([
            'associations' => [
                1234 => ['products' => ['value2', 'test2'], 'groups' => ['value', 'test']],
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
