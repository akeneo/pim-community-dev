<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DuplicateOptions;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class OptionsGuesserSpec extends ObjectBehavior
{
    function it_is_a_constraint_guesser(): void
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    function it_only_supports_multiselect_attribute_types(AttributeInterface $name, AttributeInterface $colors): void
    {
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $colors->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);

        $this->supportAttribute($name)->shouldReturn(false);
        $this->supportAttribute($colors)->shouldReturn(true);
    }

    function it_guesses_constraints(AttributeInterface $attribute): void
    {
        $this->guessConstraints($attribute)->shouldBeLike([new DuplicateOptions()]);
    }
}
