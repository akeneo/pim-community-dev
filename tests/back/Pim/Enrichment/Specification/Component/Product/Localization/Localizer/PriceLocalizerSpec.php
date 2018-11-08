<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceLocalizerSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, NumberFactory $numberFactory)
    {
        $this->beConstructedWith($validator, $numberFactory, ['pim_catalog_price_collection']);
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement(LocalizerInterface::class);
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_price_collection')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $prices = [
            ['amount' => '10.05', 'currency' => 'EUR'],
            ['amount' => '-10.05', 'currency' => 'USD'],
            ['amount' => '10', 'currency' => 'USD'],
            ['amount' => '-10', 'currency' => 'EUR'],
            ['amount' => 10, 'currency' => 'EUR'],
            ['amount' => 10.05, 'currency' => 'USD'],
            ['amount' => ' 10.05 ', 'currency' => 'PES'],
            ['amount' => null, 'currency' => null],
            ['amount' => '', 'currency' => ''],
            ['amount' => 0, 'currency' => 'PES'],
            ['amount' => '0', 'currency' => 'PES'],
        ];
        $this->validate($prices, 'prices', ['decimal_separator' => '.'])->shouldReturn(null);
    }


    function it_returns_a_constraint_if_the_decimal_separator_is_not_valid($validator)
    {
        $constraintUSD = new ConstraintViolation('Error on number validator', '', [], '', '', '');
        $constraintEUR = new ConstraintViolation('Error on number validator', '', [], '', '', '');
        $constraints = new ConstraintViolationList([$constraintEUR, $constraintUSD]);
        $validator->validate('10,00', Argument::any())->willReturn($constraints);
        $validator->validate('10,05', Argument::any())->willReturn(null);

        $prices = [['amount' => '10,00', 'currency' => 'EUR'], ['amount' => '10,05', 'currency' => 'USD']];

        $allConstraints = new ConstraintViolationList();
        $allConstraints->addAll($constraints);
        $this->validate($prices, 'prices', ['decimal_separator' => ','])
            ->shouldHaveCount(2);
    }

    function it_converts()
    {
        $prices = [
            ['amount' => '10,05', 'currency' => 'EUR'],
            ['amount' => '-10,05', 'currency' => 'EUR'],
            ['amount' => '10', 'currency' => 'PES'],
            ['amount' => '-10', 'currency' => 'PES'],
            ['amount' => 10, 'currency' => 'PES'],
            ['amount' => 10.05, 'currency' => 'PES'],
            ['amount' => ' 10.05 ', 'currency' => 'PES'],
            ['amount' => null, 'currency' => null],
            ['amount' => '', 'currency' => ''],
            ['amount' => 0, 'currency' => 'EUR'],
            ['amount' => '0', 'currency' => 'EUR'],
            ['amount' => 'gruik', 'currency' => 'EUR']
        ];

        $this->delocalize($prices, ['decimal_separator' => ','])->shouldReturn(
            [
                ['amount' => '10.05', 'currency' => 'EUR'],
                ['amount' => '-10.05', 'currency' => 'EUR'],
                ['amount' => '10', 'currency' => 'PES'],
                ['amount' => '-10', 'currency' => 'PES'],
                ['amount' => 10, 'currency' => 'PES'],
                ['amount' => '10.05', 'currency' => 'PES'],
                ['amount' => ' 10.05 ', 'currency' => 'PES'],
                ['amount' => null, 'currency' => null],
                ['amount' => null, 'currency' => ''],
                ['amount' => 0, 'currency' => 'EUR'],
                ['amount' => '0', 'currency' => 'EUR'],
                ['amount' => 'gruik', 'currency' => 'EUR']
            ]
        );

        $this->delocalize([['amount' => '10,00']], [], 'prices')->shouldReturn([['amount' => '10.00']]);
        $this->delocalize([['amount' => '10,00']], ['decimal_separator' => null], 'prices')
            ->shouldReturn([['amount' => '10.00']]);
        $this->delocalize([['amount' => '10,00']], ['decimal_separator' => ''], 'prices')
            ->shouldReturn([['amount' => '10.00']]);
    }
}
