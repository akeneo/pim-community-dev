<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
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
        $this->isValid('10.05', ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid('-10.05', ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid('10', ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid('-10', ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(10, ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(10.0585, ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(' 10.05 ', ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(null, ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid('', ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid('0', ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(0, ['decimal_separator' => '.'])->shouldReturn(true);
    }

    function it_does_not_valid_the_decimal_separator()
    {
        $this->isValid('10.00', ['decimal_separator' => ','])->shouldReturn(false);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->convertLocalizedToDefault('10,05')->shouldReturn('10.05');
        $this->convertLocalizedToDefault('-10,05')->shouldReturn('-10.05');
        $this->convertLocalizedToDefault('10')->shouldReturn('10');
        $this->convertLocalizedToDefault('-10')->shouldReturn('-10');
        $this->convertLocalizedToDefault(10)->shouldReturn('10');
        $this->convertLocalizedToDefault(10.0585)->shouldReturn('10.0585');
        $this->convertLocalizedToDefault(' 10,05 ')->shouldReturn(' 10.05 ');
        $this->convertLocalizedToDefault(null)->shouldReturn(null);
        $this->convertLocalizedToDefault('')->shouldReturn('');
        $this->convertLocalizedToDefault(0)->shouldReturn('0');
        $this->convertLocalizedToDefault('0')->shouldReturn('0');
    }

    function it_fails_if_decimal_separator_is_missing()
    {
        $exception = new MissingOptionsException('The required option "decimal_separator" is missing.');
        $this->shouldThrow($exception)
            ->during('isValid', ['10.00', []]);
    }

    function it_fails_if_decimal_separator_is_empty()
    {
        $message = 'The option "decimal_separator" with value null is expected to be of type "string", ';
        $message.= 'but is of type "NULL".';
        $exception = new InvalidOptionsException($message);
        $this->shouldThrow($exception)
            ->during('isValid', ['10.00', ['decimal_separator' => null]]);
    }
}
