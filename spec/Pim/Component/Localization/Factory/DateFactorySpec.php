<?php

namespace spec\Pim\Component\Localization\Factory;

use PhpSpec\ObjectBehavior;

class DateFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['en_US' => 'm/d/Y', 'fr_FR' => 'dd/MM/yyyy']);
    }

    function it_creates_a_date_formatter()
    {
        $date = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            null,
            null,
            'dd/MM/yyyy'
        );

        $this->create(['locale' => 'fr_FR'])->shouldReturnAnInstanceOf(get_class($date));
        $this->create(['locale' => 'fr_FR'])->getPattern()->shouldReturn('dd/MM/yyyy');
    }
}
