<?php

namespace spec\Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\TranslatorInterface;

class DatePresenterSpec extends ObjectBehavior
{
    function let(DateFactory $dateFactory, TranslatorInterface $translator)
    {
        $this->beConstructedWith($dateFactory, ['pim_catalog_date'], $translator);
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
}
