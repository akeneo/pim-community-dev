<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

class ReferenceEntityIdentifierSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedThrough('fromString', ['a_reference_entity_identifier']);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ReferenceEntityIdentifier::class);
    }

    function it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_displayed_as_a_string(): void
    {
        $this->asString()->shouldBe('a_reference_entity_identifier');
    }

    function it_can_be_compared_to_another_identifier(): void
    {
        $this->equals(ReferenceEntityIdentifier::fromString('a_reference_entity_identifier'))->shouldBe(true);
        $this->equals(ReferenceEntityIdentifier::fromString('a_Reference_Entity_Identifier'))->shouldBe(true);
        $this->equals(ReferenceEntityIdentifier::fromString('another_identifier'))->shouldBe(false);
    }
}
