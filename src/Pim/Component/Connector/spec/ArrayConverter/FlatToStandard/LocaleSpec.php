<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class LocaleSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\FlatToStandard\Locale'
        );
    }

    function it_converts_an_activated_item_to_standard_format()
    {
        $this->convert(['code' => 'en_US', 'activated' => 1])->shouldReturn(['code' => 'en_US', 'enabled' => true]);
    }

    function it_converts_a_disabled_item_to_standard_format()
    {
        $this->convert(['code' => 'en_US', 'activated' => 0])->shouldReturn(['code' => 'en_US', 'enabled' => false]);
    }
}
