<?php

namespace spec\Akeneo\Pim\Structure\Component\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;

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
        $this->shouldHaveType('Akeneo\Pim\Structure\Component\AttributeTypeInterface');
    }
}
