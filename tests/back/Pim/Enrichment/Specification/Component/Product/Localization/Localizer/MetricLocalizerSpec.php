<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MetricLocalizerSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, NumberFactory $numberFactory)
    {
        $this->beConstructedWith($validator, $numberFactory, ['pim_catalog_metric']);
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement(LocalizerInterface::class);
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_metric')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_price_collection')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $this->validate(['amount' => '10.05', 'unit' => 'KILOGRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => '-10.05', 'unit' => 'KILOGRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => '10', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => '-10', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => 10, 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => 10.05, 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => ' 10.05 ', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => null, 'unit' => null], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => '', 'unit' => ''], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => 0, 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
        $this->validate(['amount' => '0', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => '.'])
            ->shouldReturn(null);
    }

    function it_returns_a_constraint_if_the_decimal_separator_is_not_valid(
        $validator,
        ConstraintViolationListInterface $constraints
    ) {
        $validator->validate(Argument::any(), Argument::any())->willReturn($constraints);

        $this->validate(['amount' => '10.00', 'unit' => 'GRAM'], 'metric', ['decimal_separator' => ','])
            ->shouldReturn($constraints);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->delocalize(['amount' => '10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => '10.05', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => '-10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => '-10.05', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => '10', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => '-10', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => 10, 'unit' => 'GRAM']);

        $this->delocalize(['amount' => 10.0585, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => '10.0585', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => ' 10.05 ', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => null, 'unit' => null], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => null, 'unit' => null]);

        $this->delocalize(['amount' => '', 'unit' => ''], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => null, 'unit' => '']);

        $this->delocalize(['amount' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => 0, 'unit' => 'GRAM']);

        $this->delocalize(['amount' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => '0', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => 'gruik', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['amount' => 'gruik', 'unit' => 'GRAM']);

        $this->delocalize([], ['decimal_separator' => '.'])
            ->shouldReturn([]);

        $this->delocalize(['amount' => '10,00', 'unit' => 'GRAM'], [])
            ->shouldReturn(['amount' => '10.00', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => '10,00', 'unit' => 'GRAM'], ['decimal_separator' => null])
            ->shouldReturn(['amount' => '10.00', 'unit' => 'GRAM']);

        $this->delocalize(['amount' => '10,00', 'unit' => 'GRAM'], ['decimal_separator' => ''])
            ->shouldReturn(['amount' => '10.00', 'unit' => 'GRAM']);
    }
}
