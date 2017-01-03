<?php

namespace spec\Pim\Component\Catalog\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;

class AttributeRequirementFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Factory\AttributeRequirementFactory');
    }

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
