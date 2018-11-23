<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferenceEntityCollectionTypeSpec extends ObjectBehavior {
    function let() {
        $this->beConstructedWith('akeneo_reference_entity_collection');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionType::class);
    }

    function it_provide_a_name()
    {
        $this->getName()->shouldReturn('akeneo_reference_entity_collection');
    }
}
