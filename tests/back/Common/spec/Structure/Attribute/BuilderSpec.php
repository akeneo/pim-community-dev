<?php

namespace spec\Akeneo\Test\Common\Structure\Attribute;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;

class BuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\Akeneo\Test\Common\Structure\Attribute\Builder::class);
    }

    function it_builds_an_attribute_with_default_value()
    {
        $attribute = $this->build();
        $attribute->getType()->shouldReturn(AttributeTypes::IDENTIFIER);
        $attribute->getCode()->shouldReturn('code');
    }

    function it_builds_an_attribute()
    {
        $this->withCode('metric');
        $this->withType(AttributeTypes::METRIC);

        $attribute = $this->build();
        $attribute->getType()->shouldReturn(AttributeTypes::METRIC);
        $attribute->getCode()->shouldReturn('metric');
    }

    function its_code_is_mutable()
    {
        $this->withCode('code')->shouldReturn($this);
    }

    function its_type_is_mutable()
    {
        $this->withType(AttributeTypes::METRIC)->shouldReturn($this);
    }
}
