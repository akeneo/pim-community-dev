<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

class AttributeRequirementFactorySpec extends ObjectBehavior
{
    function it_creates_an_attribute_requirement(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $this->createAttributeRequirement($attribute, $channel, true)
            ->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');
    }
}
