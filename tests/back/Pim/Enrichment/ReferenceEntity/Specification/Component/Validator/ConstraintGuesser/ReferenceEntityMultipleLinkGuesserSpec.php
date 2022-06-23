<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\DuplicateRecords;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityMultipleLinkGuesserSpec extends ObjectBehavior
{
    public function it_supports_the_reference_entity_multiple_link_attribute(
        AttributeInterface $booleanAttribute,
        AttributeInterface $assetMultipleLinkAttribute
    ) {
        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $assetMultipleLinkAttribute->getType()->willReturn(AttributeTypes::REFERENCE_ENTITY_COLLECTION);

        $this->supportAttribute($booleanAttribute)->shouldReturn(false);
        $this->supportAttribute($assetMultipleLinkAttribute)->shouldReturn(true);
    }

    public function it_guesses_the_constraints_for_the_attribute(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('an_attribute_code');

        $constraints = $this->guessConstraints($attribute);
        $constraints->shouldBeArray();
        $constraints[0]->shouldBeAnInstanceOf(DuplicateRecords::class);
    }
}
