<?php

namespace spec\Akeneo\Pim\EnrichedEntity\Component\AttributeType;

use Akeneo\Pim\EnrichedEntity\Component\AttributeType\EnrichedEntityCollectionType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnrichedEntityCollectionTypeSpec extends ObjectBehavior {
    function let() {
        $this->beConstructedWith('akeneo_enriched_entity_collection');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntityCollectionType::class);
    }

    function it_provide_a_name()
    {
        $this->getName()->shouldReturn('akeneo_enriched_entity_collection');
    }
}
