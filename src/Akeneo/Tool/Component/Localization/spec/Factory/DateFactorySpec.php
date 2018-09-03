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
        $date = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE
        );

        $this->create([])->shouldReturnAnInstanceOf(get_class($date));
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

    function it_creates_an_english_date_without_locale()
    {
        $options = [];
        $this->create($options)->getPattern()->shouldReturn('M/d/yy');
    }
}
