<?php

namespace Specification\Akeneo\Pim\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeTypeInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class FileTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_MEDIA);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_file');
    }

    function it_is_an_attribute_type()
    {
        $this->shouldHaveType(AttributeTypeInterface::class);
    }
}
