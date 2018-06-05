<?php

namespace spec\Pim\Component\Catalog\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
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
            'Akeneo\Pim\Structure\Component\Model\Family'
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
        $family->shouldBeAnInstanceOf('Akeneo\Pim\Structure\Component\Model\FamilyInterface');
        $family->getAttributes()->shouldHaveCount(1);
        $family->getAttributes()->first()->shouldBeEqualTo($identifierAttribute);
        $family->getAttributeRequirements()->shouldHaveCount(2);
        $family->getAttributeRequirements()->shouldBeEqualTo([
            'anyCode_print' => $printRequirement,
            'anyCode_ecommerce' => $ecommerceRequirement
        ]);
    }
}
