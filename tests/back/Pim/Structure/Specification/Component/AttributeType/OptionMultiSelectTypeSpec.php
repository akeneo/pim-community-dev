<?php

namespace Specification\Akeneo\Pim\Structure\Component\AttributeType;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class OptionMultiSelectTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_OPTIONS);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_multiselect');
    }
}
