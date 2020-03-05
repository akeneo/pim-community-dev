<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Validator\Constraint\ConversionUnits;
use Symfony\Component\Validator\Constraint;

class ConversionUnitsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConversionUnits::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_invalid_attribute_code_message()
    {
        $this->invalidAttributeCode->shouldBe('The attribute "%attributeCode%" does not exist.');
    }

    function it_has_not_a_metric_attribute_message()
    {
        $this->notAMetricAttribute->shouldBe('The attribute "%attributeCode%" is not a metric attribute.');
    }

    function it_has_invalid_unit_code_message()
    {
        $this->invalidUnitCode->shouldBe('The unit "%unitCode%" does not exist or does not belong to the default metric family of the given attribute "%attributeCode%".');
    }
}
