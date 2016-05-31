<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Prophecy\Argument;

class LocaleSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $checker)
    {
        $this->beConstructedWith($checker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\ArrayConverterInterface'
        );
    }

    function it_converts_an_item_to_standard_format($checker)
    {
        $checker->checkFieldsPresence(Argument::any(), ['code'])->shouldBeCalled();
        $checker->checkFieldsFilling(Argument::any(), ['code'])->shouldBeCalled();

        $this->convert(['code' => 'en_US', 'foo' => 'bar'])
            ->shouldReturn(['code' => 'en_US']);
    }
}
