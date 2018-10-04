<?php

namespace Specification\Akeneo\Pim\Structure\Component\AttributeType;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class DateTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_DATE);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_date');
    }
}
