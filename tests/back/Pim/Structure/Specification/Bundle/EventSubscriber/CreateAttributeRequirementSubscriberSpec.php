<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateAttributeRequirementSubscriberSpec extends ObjectBehavior
{
    function let(
        AttributeRequirementFactory $requirementFactory,
    ) {
        $this->beConstructedWith($requirementFactory);
    }

    public function it_is_a_doctrine_event_subscriber()
    {
        $this->shouldImplement(EventSubscriber::class);
    }

    public function it_subscribes_to_prePersist()
    {
        $this->getSubscribedEvents()->shouldReturn(['prePersist']);
    }

    public function it_ignores_non_channel_entity(
        AttributeRequirementFactory $requirementFactory,
        EntityManagerInterface $entityManager,
    ) {
        $entityManager->getRepository(Argument::any())->shouldNotBeCalled();
        $requirementFactory->createAttributeRequirement(Argument::cetera())->shouldNotBeCalled();

        $this->prePersist(new PrePersistEventArgs(new Attribute(), $entityManager->getWrappedObject()));
    }

    public function it_creates_requirements_for_the_attribute_defined_as_identifier(
        AttributeRequirementFactory $requirementFactory,
        EntityManagerInterface $entityManager,
        ChannelInterface $channel,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        FamilyInterface $familyA,
        FamilyInterface $familyB,
        AttributeInterface $identifierAttribute,
        AttributeRequirementInterface $attributeRequirementA,
        AttributeRequirementInterface $attributeRequirementB
    ) {
        $entityManager->getRepository(FamilyInterface::class)->willReturn($familyRepository);
        $entityManager->getRepository(AttributeInterface::class)->willReturn($attributeRepository);

        $familyRepository->findAll()->willReturn([$familyA, $familyB]);
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);

        $requirementFactory
            ->createAttributeRequirement($identifierAttribute, $channel, true)
            ->willReturn($attributeRequirementA, $attributeRequirementB);

        $attributeRequirementA->setFamily($familyA)->shouldBeCalled();
        $attributeRequirementB->setFamily($familyB)->shouldBeCalled();

        $entityManager->persist($attributeRequirementA)->shouldBeCalled();
        $entityManager->persist($attributeRequirementB)->shouldBeCalled();

        $this->prePersist(new PrePersistEventArgs($channel->getWrappedObject(), $entityManager->getWrappedObject()));
    }
}
