<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntity;
use Symfony\Component\Validator\Constraint;

class UniqueProductModelEntitySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueProductModelEntity::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_is_validated_by_a_specific_validator()
    {
        $this->validatedBy()->shouldReturn('pim_unique_product_model_validator_entity');
    }

    function it_validates_a_class()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}
