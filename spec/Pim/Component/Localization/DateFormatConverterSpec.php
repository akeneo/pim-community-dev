<?php

namespace spec\Pim\Component\Localization\Converter;

use PhpSpec\ObjectBehavior;

class DateFormatConverterSpec extends ObjectBehavior
{
    function it_should_convert_dates()
    {
        $this->convert('yy/MM/dd')->shouldReturn('y/m/d');
        $this->convert('dd/MM/y')->shouldReturn('d/m/Y');
        $this->convert('M/d/yy')->shouldReturn('n/j/y');
    }
}
