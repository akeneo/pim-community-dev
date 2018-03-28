<?php

namespace spec\Pim\Component\Catalog\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Prophecy\Argument;

class FamilyFactorySpec extends ObjectBehavior
{
    function let(
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $factory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $channelRepository,
            $factory,
            $attributeRepository,
            'Pim\Bundle\CatalogBundle\Entity\Family'
        );
    }

    function it_creates_a_family(
        $attributeRepository,
        $channelRepository,
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

        $channelRepository->findAll()
            ->willReturn([$printChannel, $ecommerceChannel])
            ->shouldBeCalled();

        $factory->createAttributeRequirement($identifierAttribute, $printChannel, true)
            ->willReturn($printRequirement)
            ->shouldBeCalled();

        $factory->createAttributeRequirement($identifierAttribute, $ecommerceChannel, true)
            ->willReturn($ecommerceRequirement)
            ->shouldBeCalled();

        $family = $this->create();
        $family->shouldBeAnInstanceOf('Pim\Component\Catalog\Model\FamilyInterface');
        $family->getAttributes()->shouldHaveCount(1);
        $family->getAttributes()->first()->shouldBeEqualTo($identifierAttribute);
        $family->getAttributeRequirements()->shouldHaveCount(2);
        $family->getAttributeRequirements()->shouldBeEqualTo([
            'anyCode_print' => $printRequirement,
            'anyCode_ecommerce' => $ecommerceRequirement
        ]);
    }
}
