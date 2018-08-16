<?php

namespace spec\Akeneo\Test\Common\Structure\Attribute;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;

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
        $this->aIdentifier();

        $attribute = $this->build();
        $attribute->getType()->shouldReturn(AttributeTypes::IDENTIFIER);
        $attribute->getCode()->shouldReturn('metric');
    }

    function its_code_is_mutable()
    {
        $this->withCode('code')->shouldReturn($this);
    }

    function it_will_build_an_identifier()
    {
        $this->aIdentifier()->shouldReturn($this);
    }
}
