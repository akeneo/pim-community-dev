<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Exception\FormatLocalizerException;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class NumberLocalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['pim_catalog_number']
        );
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement('Pim\Component\Localization\Localizer\LocalizerInterface');
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_number')->shouldReturn(true);
        $this->supports('pim_catalog_metric')->shouldReturn(false);
        $this->supports('pim_catalog_price_collection')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $this->isValid('10.05', ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid('-10.05', ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid('10', ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid('-10', ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid(10, ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid(10.0585, ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid(' 10.05 ', ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid(null, ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid('', ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid('0', ['decimal_separator' => '.'], 'number')->shouldReturn(true);
        $this->isValid(0, ['decimal_separator' => '.'], 'number')->shouldReturn(true);
    }

    function it_throws_an_exception_if_the_decimal_separator_is_not_valid()
    {
        $exception = new FormatLocalizerException('number', ',');
        $this->shouldThrow($exception)
            ->during('isValid', ['10.00', ['decimal_separator' => ','], 'number']);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->delocalize('10,05', ['decimal_separator' => '.'])->shouldReturn('10.05');
        $this->delocalize('-10,05', ['decimal_separator' => '.'])->shouldReturn('-10.05');
        $this->delocalize('10', ['decimal_separator' => '.'])->shouldReturn('10');
        $this->delocalize('-10', ['decimal_separator' => '.'])->shouldReturn('-10');
        $this->delocalize(10, ['decimal_separator' => '.'])->shouldReturn(10);
        $this->delocalize(10.0585, ['decimal_separator' => '.'])->shouldReturn('10.0585');
        $this->delocalize(' 10,05 ', ['decimal_separator' => '.'])->shouldReturn(' 10.05 ');
        $this->delocalize(null, ['decimal_separator' => '.'])->shouldReturn(null);
        $this->delocalize('', ['decimal_separator' => '.'])->shouldReturn('');
        $this->delocalize(0, ['decimal_separator' => '.'])->shouldReturn(0);
        $this->delocalize('0', ['decimal_separator' => '.'])->shouldReturn('0');
    }

    function it_throws_an_exception_if_decimal_separator_is_missing()
    {
        $exception = new MissingOptionsException('The option "decimal_separator" do not exist.');
        $this->shouldThrow($exception)
            ->during('isValid', ['10.00', [], 'number']);

        $this->shouldThrow($exception)
            ->during('isValid', ['10.00', ['decimal_separator' => null], 'number']);

        $this->shouldThrow($exception)
            ->during('isValid', ['10.00', ['decimal_separator' => ''], 'number']);
    }
}
