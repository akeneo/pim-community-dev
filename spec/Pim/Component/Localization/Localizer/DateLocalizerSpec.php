<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\LocalizationBundle\Validator\Constraints\DateFormat;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DateLocalizerSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, FormatProviderInterface $formatProvider, DateFormat $dateConstraint)
    {
        $this->beConstructedWith($validator, $formatProvider, $dateConstraint, ['pim_catalog_date']);
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
        $this->validate('28/10/2015', ['date_format' => 'd/m/Y'], 'date')->shouldReturn(null);
        $this->validate('01/10/2015', ['date_format' => 'd/m/Y'], 'date')->shouldReturn(null);
        $this->validate('2015/10/25', ['date_format' => 'Y/m/d'], 'date')->shouldReturn(null);
        $this->validate('2015/10/01', ['date_format' => 'Y/m/d'], 'date')->shouldReturn(null);
        $this->validate('2015-10-25', ['date_format' => 'Y-m-d'], 'date')->shouldReturn(null);
        $this->validate('2015-10-01', ['date_format' => 'Y-m-d'], 'date')->shouldReturn(null);
        $this->validate('', ['date_format' => 'Y-m-d'], 'date')->shouldReturn(null);
        $this->validate(null, ['date_format' => 'Y-m-d'], 'date')->shouldReturn(null);
    }

    function it_returns_a_constraint_if_the_format_is_not_valid(
        $validator,
        ConstraintViolationListInterface $constraints
    ) {
        $validator->validate('28/10/2015', Argument::any())->willReturn($constraints);

        $this->validate('28/10/2015', ['date_format' => 'd-m-Y'], 'date')->shouldReturn($constraints);
    }

    function it_delocalize_with_date_format_option()
    {
        $this->delocalize('28/10/2015', ['date_format' => 'd/m/Y'])->shouldReturn('2015-10-28');
        $this->delocalize('28-10-2015', ['date_format' => 'd-m-Y'])->shouldReturn('2015-10-28');
        $this->delocalize('2015-10-28', ['date_format' => 'Y-m-d'])->shouldReturn('2015-10-28');
        $this->delocalize('2015/10/28', ['date_format' => 'Y/m/d'])->shouldReturn('2015-10-28');
    }
}
