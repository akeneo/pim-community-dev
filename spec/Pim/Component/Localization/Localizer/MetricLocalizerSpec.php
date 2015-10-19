<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Exception\FormatLocalizerException;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class MetricLocalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['pim_catalog_metric']
        );
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
        $this->isValid(['data' => '10.05', 'unit' => 'KILOGRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => '-10.05', 'unit' => 'KILOGRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => 10.05, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => null, 'unit' => null], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => '', 'unit' => ''], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
        $this->isValid(['data' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(true);
    }

    function it_throws_an_exception_if_the_decimal_separator_is_not_valid()
    {
        $exception = new FormatLocalizerException('metric', ',');
        $this->shouldThrow($exception)
            ->during('isValid', [['data' => '10.00', 'unit' => 'GRAM'], ['decimal_separator' => ','], 'metric']);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->convertLocalizedToDefault(['data' => '10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10.05', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => '-10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '-10.05', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '-10', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 10, 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => 10.0585, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10.0585', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => ' 10.05 ', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => null, 'unit' => null], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => null, 'unit' => null]);

        $this->convertLocalizedToDefault(['data' => '', 'unit' => ''], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '', 'unit' => '']);

        $this->convertLocalizedToDefault(['data' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 0, 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '0', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault([], ['decimal_separator' => '.'])
            ->shouldReturn([]);
    }

    function it_throws_an_exception_if_decimal_separator_is_missing()
    {
        $exception = new MissingOptionsException('The option "decimal_separator" do not exist.');
        $this->shouldThrow($exception)
            ->during('isValid', [['data' => '10.00'], [], 'metric']);

        $this->shouldThrow($exception)
            ->during('isValid', [['data' => '10.00'], ['decimal_separator' => null], 'metric']);

        $this->shouldThrow($exception)
            ->during('isValid', [['data' => '10.00'], ['decimal_separator' => ''], 'metric']);
    }
}
