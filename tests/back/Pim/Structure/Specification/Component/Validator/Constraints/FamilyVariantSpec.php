<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyVariant;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class FamilyVariantSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariant::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_is_validated_by_a_validator()
    {
        $this->validatedBy()->shouldReturn('pim_family_variant');
    }

    function it_validates_a_class()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}
