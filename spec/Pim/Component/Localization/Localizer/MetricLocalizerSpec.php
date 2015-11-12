<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\LocalizationBundle\Validator\Constraints\NumberFormat;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MetricLocalizerSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, FormatProviderInterface $formatProvider, NumberFormat $numberConstraint)
    {
        $this->beConstructedWith($validator, $formatProvider, $numberConstraint, ['pim_catalog_metric']);
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement('Pim\Component\Localization\Localizer\LocalizerInterface');
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_metric')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_price_collection')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $this->validate(['data' => '10.05', 'unit' => 'KILOGRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '-10.05', 'unit' => 'KILOGRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => 10.05, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => null, 'unit' => null], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '', 'unit' => ''], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
    }

    function it_returns_a_constraint_if_the_decimal_separator_is_not_valid(
        $validator,
        ConstraintViolationListInterface $constraints
    ) {
        $validator->validate(Argument::any(), Argument::any())->willReturn($constraints);

        $this->validate(['data' => '10.00', 'unit' => 'GRAM'], ['decimal_separator' => ','], 'metric')
            ->shouldReturn($constraints);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->delocalize(['data' => '10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10.05', 'unit' => 'GRAM']);

        $this->delocalize(['data' => '-10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '-10.05', 'unit' => 'GRAM']);

        $this->delocalize(['data' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10', 'unit' => 'GRAM']);

        $this->delocalize(['data' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '-10', 'unit' => 'GRAM']);

        $this->delocalize(['data' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 10, 'unit' => 'GRAM']);

        $this->delocalize(['data' => 10.0585, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10.0585', 'unit' => 'GRAM']);

        $this->delocalize(['data' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => ' 10.05 ', 'unit' => 'GRAM']);

        $this->delocalize(['data' => null, 'unit' => null], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => null, 'unit' => null]);

        $this->delocalize(['data' => '', 'unit' => ''], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '', 'unit' => '']);

        $this->delocalize(['data' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 0, 'unit' => 'GRAM']);

        $this->delocalize(['data' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '0', 'unit' => 'GRAM']);

        $this->delocalize([], ['decimal_separator' => '.'])
            ->shouldReturn([]);

        $this->delocalize(['data' => '10,00', 'unit' => 'GRAM'], [])
            ->shouldReturn(['data' => '10.00', 'unit' => 'GRAM']);

        $this->delocalize(['data' => '10,00', 'unit' => 'GRAM'], ['decimal_separator' => null])
            ->shouldReturn(['data' => '10.00', 'unit' => 'GRAM']);

        $this->delocalize(['data' => '10,00', 'unit' => 'GRAM'], ['decimal_separator' => ''])
            ->shouldReturn(['data' => '10.00', 'unit' => 'GRAM']);
    }
}
