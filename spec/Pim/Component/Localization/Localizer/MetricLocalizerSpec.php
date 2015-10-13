<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
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
        $this->isValid(['data' => '10.05', 'unit' => 'KILOGRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => '-10.05', 'unit' => 'KILOGRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => 10.05, 'unit' => 'GRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => null, 'unit' => null], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => '', 'unit' => ''], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
        $this->isValid(['data' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'])->shouldReturn(true);
    }

    function it_does_not_valid_the_decimal_separator()
    {
        $this->isValid(['data' => '10.00', 'unit' => 'GRAM'], ['decimal_separator' => ','])->shouldReturn(false);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->convertLocalizedToDefault(['data' => '10,05', 'unit' => 'GRAM'])
            ->shouldReturn(['data' => '10.05', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => '-10,05', 'unit' => 'GRAM'])
            ->shouldReturn(['data' => '-10.05', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => '10', 'unit' => 'GRAM'])
            ->shouldReturn(['data' => '10', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => '-10', 'unit' => 'GRAM'])
            ->shouldReturn(['data' => '-10', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => 10, 'unit' => 'GRAM'])
            ->shouldReturn(['data' => '10', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => 10.0585, 'unit' => 'GRAM'])
            ->shouldReturn(['data' => '10.0585', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => ' 10.05 ', 'unit' => 'GRAM'])
            ->shouldReturn(['data' => ' 10.05 ', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => null, 'unit' => null])
            ->shouldReturn(['data' => null, 'unit' => null]);

        $this->convertLocalizedToDefault(['data' => '', 'unit' => ''])
            ->shouldReturn(['data' => '', 'unit' => '']);

        $this->convertLocalizedToDefault(['data' => 0, 'unit' => 'GRAM'])
            ->shouldReturn(['data' => '0', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault(['data' => '0', 'unit' => 'GRAM'])
            ->shouldReturn(['data' => '0', 'unit' => 'GRAM']);

        $this->convertLocalizedToDefault([])
            ->shouldReturn([]);
    }

    function it_fails_if_decimal_separator_is_missing()
    {
        $exception = new MissingOptionsException('The required option "decimal_separator" is missing.');
        $this->shouldThrow($exception)
            ->during('isValid', [['data' => '10.00'], []]);
    }

    function it_fails_if_decimal_separator_is_empty()
    {
        $message = 'The option "decimal_separator" with value null is expected to be of type "string", ';
        $message.= 'but is of type "NULL".';
        $exception = new InvalidOptionsException($message);
        $this->shouldThrow($exception)

            ->during('isValid', [['data' => '10.00'], ['decimal_separator' => null]]);
    }
}
