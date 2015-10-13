<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class PriceLocalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['pim_catalog_price_collection']
        );
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement('Pim\Component\Localization\Localizer\LocalizerInterface');
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_price_collection')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $prices = [
            ['data' => '10.05', 'currency' => 'EUR'],
            ['data' => '-10.05', 'currency' => 'USD'],
            ['data' => '10', 'currency' => 'USD'],
            ['data' => '-10', 'currency' => 'EUR'],
            ['data' => 10, 'currency' => 'EUR'],
            ['data' => 10.05, 'currency' => 'USD'],
            ['data' => ' 10.05 ', 'currency' => 'PES'],
            ['data' => null, 'currency' => null],
            ['data' => '', 'currency' => ''],
            ['data' => 0, 'currency' => 'PES'],
            ['data' => '0', 'currency' => 'PES'],
        ];
        $this->isValid($prices, ['decimal_separator' => '.'])->shouldReturn(true);
    }

    function it_does_not_valid_the_decimal_separator()
    {
        $prices = [['data' => '10.00', 'currency' => 'EUR'], ['data' => '10,05', 'currency' => 'USD']];
        $this->isValid($prices, ['decimal_separator' => ','])->shouldReturn(false);
    }

    function it_convert_comma_to_dot_separator()
    {
        $prices = [
            ['data' => '10,05', 'currency' => 'EUR'],
            ['data' => '-10,05', 'currency' => 'EUR'],
            ['data' => '10', 'currency' => 'PES'],
            ['data' => '-10', 'currency' => 'PES'],
            ['data' => 10, 'currency' => 'PES'],
            ['data' => 10.05, 'currency' => 'PES'],
            ['data' => ' 10.05 ', 'currency' => 'PES'],
            ['data' => null, 'currency' => null],
            ['data' => '', 'currency' => ''],
            ['data' => 0, 'currency' => 'EUR'],
            ['data' => '0', 'currency' => 'EUR']
        ];

        $this->convertLocalizedToDefault($prices)->shouldReturn(
            [
                ['data' => '10.05', 'currency' => 'EUR'],
                ['data' => '-10.05', 'currency' => 'EUR'],
                ['data' => '10', 'currency' => 'PES'],
                ['data' => '-10', 'currency' => 'PES'],
                ['data' => '10', 'currency' => 'PES'],
                ['data' => '10.05', 'currency' => 'PES'],
                ['data' => ' 10.05 ', 'currency' => 'PES'],
                ['data' => null, 'currency' => null],
                ['data' => '', 'currency' => ''],
                ['data' => '0', 'currency' => 'EUR'],
                ['data' => '0', 'currency' => 'EUR']
            ]
        );
    }

    function it_fails_if_decimal_separator_is_missing()
    {
        $exception = new MissingOptionsException('The required option "decimal_separator" is missing.');
        $this->shouldThrow($exception)
            ->during('isValid', [[['data' => '10.00']], []]);
    }

    function it_fails_if_decimal_separator_is_empty()
    {
        $message = 'The option "decimal_separator" with value null is expected to be of type "string", ';
        $message.= 'but is of type "NULL".';
        $exception = new InvalidOptionsException($message);
        $this->shouldThrow($exception)

            ->during('isValid', [[['data' => '10.00']], ['decimal_separator' => null]]);
    }
}
