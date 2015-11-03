<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Exception\FormatLocalizerException;
use Pim\Component\Localization\Provider\FormatProviderInterface;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class DateLocalizerSpec extends ObjectBehavior
{
    function let(FormatProviderInterface $formatProvider)
    {
        $this->beConstructedWith($formatProvider, ['pim_catalog_date']);
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement('Pim\Component\Localization\Localizer\LocalizerInterface');
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_date')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $this->isValid('28/10/2015', ['date_format' => 'd/m/Y'], 'date')->shouldReturn(true);
        $this->isValid('01/10/2015', ['date_format' => 'd/m/Y'], 'date')->shouldReturn(true);
        $this->isValid('2015/10/25', ['date_format' => 'Y/m/d'], 'date')->shouldReturn(true);
        $this->isValid('2015/10/01', ['date_format' => 'Y/m/d'], 'date')->shouldReturn(true);
        $this->isValid('2015-10-25', ['date_format' => 'Y-m-d'], 'date')->shouldReturn(true);
        $this->isValid('2015-10-01', ['date_format' => 'Y-m-d'], 'date')->shouldReturn(true);
        $this->isValid('', ['date_format' => 'Y-m-d'], 'date')->shouldReturn(true);
        $this->isValid(null, ['date_format' => 'Y-m-d'], 'date')->shouldReturn(true);
    }

    function it_throws_an_exception_if_the_format_is_not_valid()
    {
        $exception = new FormatLocalizerException('date', 'd-m-Y');
        $this->shouldThrow($exception)->during('isValid', ['28/10/2015', ['date_format' => 'd-m-Y'], 'date']);
        $this->shouldThrow($exception)->during('isValid', ['1/10/2015', ['date_format' => 'd-m-Y'], 'date']);
        $this->shouldThrow($exception)->during('isValid', ['/10/2015', ['date_format' => 'd-m-Y'], 'date']);
        $this->shouldThrow($exception)->during('isValid', ['2015/10/28', ['date_format' => 'd-m-Y'], 'date']);
        $this->shouldThrow($exception)->during('isValid', ['2015/28/10', ['date_format' => 'd-m-Y'], 'date']);
    }

    function it_converts()
    {
        $this->convertLocalizedToDefault('28/10/2015', ['date_format' => 'd/m/Y'])->shouldReturn('2015-10-28');
        $this->convertLocalizedToDefault('28-10-2015', ['date_format' => 'd-m-Y'])->shouldReturn('2015-10-28');
        $this->convertLocalizedToDefault('2015-10-28', ['date_format' => 'Y-m-d'])->shouldReturn('2015-10-28');
        $this->convertLocalizedToDefault('2015/10/28', ['date_format' => 'Y/m/d'])->shouldReturn('2015-10-28');
    }

    function it_throws_an_exception_if_date_format_is_empty()
    {
        $exception = new MissingOptionsException('The option "date_format" do not exist.');
        $this->shouldThrow($exception)
            ->during('isValid', ['28/10/2015', [], 'date']);

        $this->shouldThrow($exception)
            ->during('isValid', ['28/10/2015', ['date_format' => null], 'date']);

        $this->shouldThrow($exception)
            ->during('isValid', ['28/10/2015', ['date_format' => ''], 'date']);
    }
}
