<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class FamilyFactorySpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        AttributeRequirementFactory $factory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($channelManager, $factory, $attributeRepository);
    }

    function it_creates_a_family(
        $attributeRepository,
        $channelManager,
        $factory,
        AttributeInterface $identifierAttribute,
        AttributeInterface $nameAttribute,
        ChannelInterface $printChannel,
        ChannelInterface $ecommerceChannel,
        AttributeRequirementInterface $printRequirement,
        AttributeRequirementInterface $ecommerceRequirement
    ) {
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);

        $channelManager->getChannels()->willReturn([$printChannel, $ecommerceChannel]);
        $printChannel->getId()->willReturn(3);

        $printRequirement->getAttribute()->willReturn($nameAttribute);
        $printRequirement->getChannel()->willReturn($printChannel);
        $printRequirement->getChannelCode()->willReturn('print');
        $printRequirement->getAttributeCode()->willReturn('name');
        $printRequirement->setFamily(Argument::any())->willReturn(null);

        $ecommerceRequirement->getAttribute()->willReturn($nameAttribute);
        $ecommerceRequirement->getChannel()->willReturn($ecommerceChannel);
        $ecommerceRequirement->getChannelCode()->willReturn('ecommerce');
        $ecommerceRequirement->getAttributeCode()->willReturn('name');
        $ecommerceRequirement->setFamily(Argument::any())->willReturn(null);

        $nameAttribute->getId()->willReturn(2);

        $factory->createAttributeRequirement($identifierAttribute, $printChannel, true)
            ->willReturn($printRequirement);

        $factory->createAttributeRequirement($identifierAttribute, $ecommerceChannel, true)
            ->willReturn($ecommerceRequirement);

        $family = $this->createFamily();
        $family->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\FamilyInterface');
        $family->getAttributes()->shouldHaveCount(1);
        $family->getAttributes()->first()->shouldBeEqualTo($identifierAttribute);
        $family->getIndexedAttributeRequirements()->shouldHaveCount(2);
        $family->getIndexedAttributeRequirements()->shouldBeEqualTo([
            'name_print'     => $printRequirement,
            'name_ecommerce' => $ecommerceRequirement
        ]);
        $family->getAttributeRequirements()->toArray()->shouldHaveCount(2);
        $family->getAttributeRequirements()->toArray()->shouldBeEqualTo([
            $printRequirement,
            $ecommerceRequirement
        ]);
    }
}
