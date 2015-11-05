<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Exception\FormatLocalizerException;
use Prophecy\Argument;
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
        $this->isValid($prices, ['decimal_separator' => '.'], 'prices')->shouldReturn(true);
    }

    function it_throws_an_exception_if_the_decimal_separator_is_not_valid()
    {
        $prices = [['data' => '10.00', 'currency' => 'EUR'], ['data' => '10,05', 'currency' => 'USD']];
        $exception = new FormatLocalizerException('prices', ',');
        $this->shouldThrow($exception)
            ->during('isValid', [$prices, ['decimal_separator' => ','], 'prices']);
    }

    function it_converts()
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

        $this->delocalize($prices, ['decimal_separator' => ','])->shouldReturn(
            [
                ['data' => '10.05', 'currency' => 'EUR'],
                ['data' => '-10.05', 'currency' => 'EUR'],
                ['data' => '10', 'currency' => 'PES'],
                ['data' => '-10', 'currency' => 'PES'],
                ['data' => 10, 'currency' => 'PES'],
                ['data' => '10.05', 'currency' => 'PES'],
                ['data' => ' 10.05 ', 'currency' => 'PES'],
                ['data' => null, 'currency' => null],
                ['data' => '', 'currency' => ''],
                ['data' => 0, 'currency' => 'EUR'],
                ['data' => '0', 'currency' => 'EUR']
            ]
        );
    }

    function it_throws_an_exception_if_decimal_separator_is_missing()
    {
        $exception = new MissingOptionsException('The option "decimal_separator" do not exist.');
        $this->shouldThrow($exception)
            ->during('isValid', [[['data' => '10.00']], [], 'prices']);

        $this->shouldThrow($exception)
            ->during('isValid', [[['data' => '10.00']], ['decimal_separator' => null], 'prices']);

        $this->shouldThrow($exception)
            ->during('isValid', [[['data' => '10.00']], ['decimal_separator' => ''], 'prices']);
    }
}
