<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

class AttributeRequirementFactorySpec extends ObjectBehavior
{
    function it_creates_a_required_attribute_requirement(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attributeRequirement = $this->createAttributeRequirement($attribute, $channel, true);
        $attributeRequirement->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');
        $attributeRequirement->getAttribute()->shouldBeEqualTo($attribute);
        $attributeRequirement->getChannel()->shouldBeEqualTo($channel);
        $attributeRequirement->isRequired()->shouldReturn(true);
    }

    function it_creates_an_unrequired_attribute_requirement(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attributeRequirement = $this->createAttributeRequirement($attribute, $channel, false);
        $attributeRequirement->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');
        $attributeRequirement->getAttribute()->shouldBeEqualTo($attribute);
        $attributeRequirement->getChannel()->shouldBeEqualTo($channel);
        $attributeRequirement->isRequired()->shouldReturn(false);
    }
}
