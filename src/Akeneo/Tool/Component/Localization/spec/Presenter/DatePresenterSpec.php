<?php

namespace spec\Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;
use PhpSpec\ObjectBehavior;

class DatePresenterSpec extends ObjectBehavior
{
    function let(DateFactory $dateFactory)
    {
        $this->beConstructedWith($dateFactory, ['pim_catalog_date']);
    }

    function it_supports_metric()
    {
        $this->supports('pim_catalog_date')->shouldReturn(true);
        $this->supports('foobar')->shouldReturn(false);
    }

    function it_presents_an_english_date(
        $dateFactory,
        \IntlDateFormatter $dateFormatter
    ) {
        $date = '2015-01-31';
        $datetime = new \DateTime('2015-01-31');
        $options = ['locale' => 'en_US'];
        $dateFactory->create($options)->willReturn($dateFormatter);
        $dateFormatter->format($datetime)->willReturn('01/31/2015');

        $this->present($date, $options)->shouldReturn('01/31/2015');
    }

    function it_presents_a_french_date(
        $dateFactory,
        \IntlDateFormatter $dateFormatter
    ) {
        $date = '2015-01-31';
        $datetime = new \DateTime('2015-01-31');
        $options = ['locale' => 'fr_FR'];
        $dateFactory->create($options)->willReturn($dateFormatter);
        $dateFormatter->format($datetime)->willReturn('31/01/2015');

        $this->present($date, $options)->shouldReturn('31/01/2015');
    }

    function it_does_not_present_a_date_if_the_date_can_not_be_formatted()
    {
        $date = '-001-11-30T00:00:00+00:00';
        $options = [
            'locale' => 'fr_FR',
            'date_format' => 'dd/MM/yyyy',
            'datetype'    => \IntlDateFormatter::SHORT,
            'timetype'    => \IntlDateFormatter::NONE,
            'timezone'    => null,
            'calendar'    => null,
        ];

        $this->present($date, $options)->shouldReturn(null);
    }
}
