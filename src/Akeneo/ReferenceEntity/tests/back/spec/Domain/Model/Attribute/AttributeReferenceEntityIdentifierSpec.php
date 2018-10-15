<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

class AttributeReferenceEntityIdentifierSpec extends ObjectBehavior
{
    function let(ReferenceEntityIdentifier $referenceEntityIdentifier)
    {
        $referenceEntityIdentifier->normalize()->willReturn('designer');

        $this->beConstructedThrough('fromReferenceEntityIdentifier', [$referenceEntityIdentifier]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeReferenceEntityIdentifier::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('designer');
    }

    function it_returns_its_reference_entity_identifier(ReferenceEntityIdentifier $referenceEntityIdentifier)
    {
        $this->getReferenceEntityIdentifier()->shouldReturn($referenceEntityIdentifier);
    }
}
