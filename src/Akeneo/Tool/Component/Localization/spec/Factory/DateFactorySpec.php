<?php

namespace spec\Akeneo\Tool\Component\Localization\Factory;

use PhpSpec\ObjectBehavior;

class DateFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['en_US' => 'm/d/Y', 'fr_FR' => 'dd/MM/yyyy']);
    }

    function it_returns_intl_formatter()
    {
        $this->create([])->shouldReturnAnInstanceOf(\IntlDateFormatter::class);
    }

    function it_creates_a_date_with_intl_format()
    {
        $options = ['locale' => 'fr_FR'];
        $this->create($options)->getPattern()->shouldReturn('dd/MM/yyyy');
    }

    function it_creates_a_date_with_defined_format()
    {
        $options = ['locale' => 'fr_FR', 'date_format' => 'd/M/yy'];
        $this->create($options)->getPattern()->shouldReturn('d/M/yy');
    }

    function it_replaces_2_digit_years_by_4_digit_when_the_format_is_not_specified(\IntlDateFormatter $formatter)
    {
        $options = ['locale' => 'en_AU'];
        $this->create($options, false)->getPattern()->shouldReturn('d/M/yy');
        $this->create($options, true)->getPattern()->shouldReturn('d/M/yyyy');
    }
}
