<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
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

    function it_cannot_be_created_from_non_string_extensions()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [[1, 'pdf']]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [['pdf', 0.2]]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [[new \stdClass()]]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [['']]);
    }

    function it_cannot_be_created_from_strings_containing_the_leading_separator()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [['.pdf']]);
    }

    function it_can_be_created_with_all_extensions_allowed()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [[AttributeAllowedExtensions::ALL_ALLOWED]]);
    }
}
