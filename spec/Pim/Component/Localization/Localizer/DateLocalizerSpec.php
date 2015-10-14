<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class DateLocalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['pim_catalog_date']
        );
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement('Pim\Component\Localization\Localizer\LocalizerInterface');
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_date')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $this->isValid('28/10/2015', ['format_date' => 'd/m/Y'])->shouldReturn(true);
        $this->isValid('01/10/2015', ['format_date' => 'd/m/Y'])->shouldReturn(true);
        $this->isValid('2015/10/25', ['format_date' => 'Y/m/d'])->shouldReturn(true);
        $this->isValid('2015/10/01', ['format_date' => 'Y/m/d'])->shouldReturn(true);
        $this->isValid('2015-10-25', ['format_date' => 'Y-m-d'])->shouldReturn(true);
        $this->isValid('2015-10-01', ['format_date' => 'Y-m-d'])->shouldReturn(true);
        $this->isValid('', ['format_date' => 'Y-m-d'])->shouldReturn(true);
        $this->isValid(null, ['format_date' => 'Y-m-d'])->shouldReturn(true);
    }

    function it_does_not_valid_the_format()
    {
        $this->isValid('28/10/2015', ['format_date' => 'd-m-Y'])->shouldReturn(false);
        $this->isValid('1/10/2015', ['format_date' => 'd-m-Y'])->shouldReturn(false);
        $this->isValid('/10/2015', ['format_date' => 'd-m-Y'])->shouldReturn(false);
        $this->isValid('2015/10/28', ['format_date' => 'd-m-Y'])->shouldReturn(false);
        $this->isValid('2015/28/10', ['format_date' => 'd-m-Y'])->shouldReturn(false);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->convertLocalizedToDefault('28/10/2015', ['format_date' => 'd/m/Y'])->shouldReturn('2015-10-28');
        $this->convertLocalizedToDefault('28-10-2015', ['format_date' => 'd-m-Y'])->shouldReturn('2015-10-28');
        $this->convertLocalizedToDefault('2015-10-28', ['format_date' => 'Y-m-d'])->shouldReturn('2015-10-28');
        $this->convertLocalizedToDefault('2015/10/28', ['format_date' => 'Y/m/d'])->shouldReturn('2015-10-28');
    }

    function it_fails_if_format_date_option_is_missing()
    {
        $exception = new MissingOptionsException('The required option "format_date" is missing.');
        $this->shouldThrow($exception)
            ->during('isValid', ['01/01/2016', []]);

        $exception = new MissingOptionsException('The required option "format_date" is missing.');
        $this->shouldThrow($exception)
            ->during('convertLocalizedToDefault', ['01/01/2016', []]);
    }

    function it_fails_if_format_date_is_empty()
    {
        $message = 'The option "format_date" with value null is expected to be of type "string", ';
        $message.= 'but is of type "NULL".';
        $exception = new InvalidOptionsException($message);
        $this->shouldThrow($exception)
            ->during('isValid', ['01/01/2016', ['format_date' => null]]);

        $this->shouldThrow($exception)
            ->during('convertLocalizedToDefault', ['01/01/2016', ['format_date' => null]]);
    }
}
