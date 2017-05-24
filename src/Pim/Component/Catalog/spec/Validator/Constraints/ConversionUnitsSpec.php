<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Validator\Constraints\ConversionUnits;
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
        $this->invalidAttributeCode->shouldBe('Property "conversion_units" expects a valid attributeCode. The attribute code for the conversion unit does not exist, "%attributeCode%" given.');
    }

    function it_has_invalid_unit_code_message()
    {
        $this->invalidUnitCode->shouldBe('Property "conversion_units" expects a valid unitCode. The metric unit code for the conversion unit does not exist, "%unitCode%" given.');
    }
}
