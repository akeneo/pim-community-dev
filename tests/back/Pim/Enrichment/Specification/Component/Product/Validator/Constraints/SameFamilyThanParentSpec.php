<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\SameFamilyThanParent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class SameFamilyThanParentSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SameFamilyThanParent::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_is_validated_by_a_validator()
    {
        $this->validatedBy()->shouldReturn('pim_family_same_family_than_parent');
    }

    function it_has_target()
    {
        $this->getTargets()->shouldReturn(SameFamilyThanParent::CLASS_CONSTRAINT);
    }
}
