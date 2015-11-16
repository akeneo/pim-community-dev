<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceLocalizerSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, FormatProviderInterface $formatProvider)
    {
        $this->beConstructedWith($validator, $formatProvider, ['pim_catalog_price_collection']);
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
        $this->validate($prices, ['decimal_separator' => '.'], 'prices')->shouldReturn(null);
    }


    function it_returns_a_constraint_if_the_decimal_separator_is_not_valid($validator)
    {
        $constraintUSD = new ConstraintViolation('Error on number validator', '', [], '', '', '');
        $constraintEUR = new ConstraintViolation('Error on number validator', '', [], '', '', '');
        $constraints = new ConstraintViolationList([$constraintEUR, $constraintUSD]);
        $validator->validate('10,00', Argument::any())->willReturn($constraints);
        $validator->validate('10,05', Argument::any())->willReturn(null);

        $prices = [['data' => '10,00', 'currency' => 'EUR'], ['data' => '10,05', 'currency' => 'USD']];

        $allConstraints = new ConstraintViolationList();
        $allConstraints->addAll($constraints);
        $this->validate($prices, ['decimal_separator' => ','], 'prices')
            ->shouldHaveCount(2);
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
            ['data' => '0', 'currency' => 'EUR'],
            ['data' => 'gruik', 'currency' => 'EUR']
        ];

        $this->delocalize($prices, ['decimal_separator' => ','])->shouldReturn(
            [
                ['data' => 10.05, 'currency' => 'EUR'],
                ['data' => -10.05, 'currency' => 'EUR'],
                ['data' => 10.00, 'currency' => 'PES'],
                ['data' => -10.00, 'currency' => 'PES'],
                ['data' => 10.00, 'currency' => 'PES'],
                ['data' => 10.05, 'currency' => 'PES'],
                ['data' => 10.05, 'currency' => 'PES'],
                ['data' => null, 'currency' => null],
                ['data' => '', 'currency' => ''],
                ['data' => 0.00, 'currency' => 'EUR'],
                ['data' => 0.00, 'currency' => 'EUR'],
                ['data' => 'gruik', 'currency' => 'EUR']
            ]
        );

        $this->delocalize([['data' => '10,00']], [], 'prices')->shouldReturn([['data' => 10.00]]);
        $this->delocalize([['data' => '10,00']], ['decimal_separator' => null], 'prices')
            ->shouldReturn([['data' => 10.00]]);
        $this->delocalize([['data' => '10,00']], ['decimal_separator' => ''], 'prices')
            ->shouldReturn([['data' => 10.00]]);
    }
}
