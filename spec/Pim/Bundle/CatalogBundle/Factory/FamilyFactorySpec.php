<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

class FamilyFactorySpec extends ObjectBehavior
{
    function let(
        ProductManager $productManager,
        ChannelManager $channelManager,
        AttributeRequirementFactory $factory
    ) {
        $this->beConstructedWith($productManager, $channelManager, $factory);
    }

    function it_creates_a_family(
        $productManager,
        $channelManager,
        $factory,
        AttributeInterface $identifierAttribute,
        ChannelInterface $printChannel,
        ChannelInterface $ecommerceChannel,
        AttributeRequirementInterface $requirement
    ) {
        $productManager->getIdentifierAttribute()
            ->willReturn($identifierAttribute)
            ->shouldBeCalled();

        $channelManager->getChannels()
            ->willReturn([$printChannel, $ecommerceChannel])
            ->shouldBeCalled();

        $factory->createAttributeRequirement($identifierAttribute, $printChannel, true)
            ->willReturn($requirement)
            ->shouldBeCalled();

        $factory->createAttributeRequirement($identifierAttribute, $ecommerceChannel, true)
            ->willReturn($requirement)
            ->shouldBeCalled();

        $this->createFamily()
            ->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Model\FamilyInterface');
    }
}
