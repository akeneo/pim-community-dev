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
        ChannelInterface $printChannel,
        ChannelInterface $ecommerceChannel,
        AttributeRequirementInterface $printRequirement,
        AttributeRequirementInterface $ecommerceRequirement
    ) {
        $attributeRepository->getIdentifier()
            ->willReturn($identifierAttribute)
            ->shouldBeCalled();

        $printRequirement->setFamily(Argument::any())
            ->willReturn(null);
        $printRequirement->getAttributeCode()
            ->willReturn('anyCode');
        $printRequirement->getChannelCode()
            ->willReturn('print');

        $ecommerceRequirement->setFamily(Argument::any())
            ->willReturn(null);
        $ecommerceRequirement->getAttributeCode()
            ->willReturn('anyCode');
        $ecommerceRequirement->getChannelCode()
            ->willReturn('ecommerce');

        $channelManager->getChannels()
            ->willReturn([$printChannel, $ecommerceChannel])
            ->shouldBeCalled();

        $factory->createAttributeRequirement($identifierAttribute, $printChannel, true)
            ->willReturn($printRequirement)
            ->shouldBeCalled();

        $factory->createAttributeRequirement($identifierAttribute, $ecommerceChannel, true)
            ->willReturn($ecommerceRequirement)
            ->shouldBeCalled();

        $family = $this->createFamily();
        $family->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\FamilyInterface');
        $family->getAttributes()->shouldHaveCount(1);
        $family->getAttributes()->first()->shouldBeEqualTo($identifierAttribute);
        $family->getAttributeRequirements()->shouldHaveCount(2);
        $family->getAttributeRequirements()->shouldBeEqualTo([
            'anyCode_print' => $printRequirement,
            'anyCode_ecommerce' => $ecommerceRequirement
        ]);
    }
}
