<?php

namespace Specification\Akeneo\Pim\Structure\Component;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypeInterface;

class AttributeTypeRegistrySpec extends ObjectBehavior
{
    function it_registers_an_attribute_type(AttributeTypeInterface $type)
    {
        $this->getAliases()->shouldHaveCount(0);
        $this->register('my_type', $type)->shouldReturn($this);
        $this->getAliases()->shouldHaveCount(1);
    }

    function it_throws_exception_when_try_to_fetch_a_not_registered_attribute_type()
    {
        $this->shouldThrow(new \LogicException('Attribute type "unknown" is not registered'))->duringGet('unknown');
    }
}
