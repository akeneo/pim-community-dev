<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
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
    }

    function it_cannot_be_created_with_invalid_extensions()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [['wrong_extension']]);
        $mixedExtensions = array_merge(AttributeAllowedExtensions::VALID_EXTENSIONS, ['wrong_extension']);
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [$mixedExtensions]);
    }

    function it_can_be_created_with_all_extensions_allowed()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromList', [[AttributeAllowedExtensions::ALL_ALLOWED]]);
    }
}
