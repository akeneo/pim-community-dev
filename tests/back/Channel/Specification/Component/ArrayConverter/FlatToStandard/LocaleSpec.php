<?php

namespace Specification\Akeneo\Channel\Component\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Component\ArrayConverter\FlatToStandard\Locale;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class LocaleSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            Locale::class
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
