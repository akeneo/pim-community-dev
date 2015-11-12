<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NumberLocalizerSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, FormatProviderInterface $formatProvider)
    {
        $this->beConstructedWith($validator, $formatProvider, ['pim_catalog_number']);
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
        $this->validate('10.05', ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate('-10.05', ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate('10', ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate('-10', ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate(10, ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate(10.0585, ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate(' 10.05 ', ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate(null, ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate('', ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate('0', ['decimal_separator' => '.'], 'number')->shouldReturn(null);
        $this->validate(0, ['decimal_separator' => '.'], 'number')->shouldReturn(null);
    }

    function it_returns_a_constraint_if_the_decimal_separator_is_not_valid(
        $validator,
        ConstraintViolationListInterface $constraints
    ) {
        $validator->validate('10.00', Argument::any())->willReturn($constraints);

        $this->validate('10.00', ['decimal_separator' => ','], 'number')->shouldReturn($constraints);
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
        $this->delocalize('10,00', [])->shouldReturn('10.00');
        $this->delocalize('10,00', ['decimal_separator' => null])->shouldReturn('10.00');
        $this->delocalize('10,00', ['decimal_separator' => ''])->shouldReturn('10.00');
    }
}
