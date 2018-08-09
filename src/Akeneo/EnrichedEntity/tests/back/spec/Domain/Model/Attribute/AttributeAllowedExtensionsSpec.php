<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use PhpSpec\ObjectBehavior;

class AttributeAllowedExtensionsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromList', [['png', 'jpeg', 'pdf']]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeAllowedExtensions::class);
    }

    function it_cannot_allow_non_string_extensions()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [[1, 'pdf']]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [['pdf', 0.2]]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [[new \stdClass()]]);
    }
}
