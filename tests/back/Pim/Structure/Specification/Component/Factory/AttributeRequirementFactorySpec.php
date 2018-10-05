<?php

namespace Specification\Akeneo\Pim\Structure\Component\Factory;

use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;

class AttributeRequirementFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeRequirement::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRequirementFactory::class);
    }

    function it_creates_a_required_attribute_requirement(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attributeRequirement = $this->createAttributeRequirement($attribute, $channel, true);
        $attributeRequirement->shouldBeAnInstanceOf(AttributeRequirement::class);
        $attributeRequirement->getAttribute()->shouldBeEqualTo($attribute);
        $attributeRequirement->getChannel()->shouldBeEqualTo($channel);
        $attributeRequirement->isRequired()->shouldReturn(true);
    }

    function it_creates_an_unrequired_attribute_requirement(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attributeRequirement = $this->createAttributeRequirement($attribute, $channel, false);
        $attributeRequirement->shouldBeAnInstanceOf(AttributeRequirement::class);
        $attributeRequirement->getAttribute()->shouldBeEqualTo($attribute);
        $attributeRequirement->getChannel()->shouldBeEqualTo($channel);
        $attributeRequirement->isRequired()->shouldReturn(false);
    }
}
