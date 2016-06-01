<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class CurrencySpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\FlatToStandard\Currency'
        );
    }

    function it_converts_an_activated_item_to_standard_format()
    {
        $this->convert(['code' => 'USD', 'activated' => 1])->shouldReturn(['code' => 'USD', 'activated' => true]);
    }

    function it_converts_a_disabled_item_to_standard_format()
    {
        $this->convert(['code' => 'USD', 'activated' => 0])->shouldReturn(['code' => 'USD', 'activated' => false]);
    }
}
