<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use PhpSpec\ObjectBehavior;

class ReferenceEntityTypeSpec extends ObjectBehavior
{
    function let() {
        $this->beConstructedWith('akeneo_reference_entity');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityType::class);
    }

    function it_provides_a_name()
    {
        $this->getName()->shouldReturn('akeneo_reference_entity');
    }
}
