<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Prophecy\Argument;

class CreateAttributeRequirementSubscriberSpec extends ObjectBehavior
{
    function let(
        AttributeRequirementFactory $requirementFactory,
        LifecycleEventArgs $eventArgs,
        ChannelInterface $channel,
        EntityManagerInterface $entityManager
    ) {
        $this->beConstructedWith($requirementFactory);

        $eventArgs->getEntity()->willReturn($channel);
        $eventArgs->getEntityManager()->willReturn($entityManager);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    public function it_subscribes_to_prePersist()
    {
        $this->getSubscribedEvents()->shouldReturn(['prePersist']);
    }

    public function it_ignores_non_ChannelInterface_entity(
        $eventArgs,
        $entityManager
    ) {
        $eventArgs->getEntity()->willReturn(null);
        $entityManager->persist(Argument::any())->shouldNotBeCalled();

        $this->prePersist($eventArgs)->shouldReturn(null);
    }

    public function it_creates_requirements_for_the_attribute_defined_as_identifier(
        $requirementFactory,
        $eventArgs,
        $channel,
        $entityManager,
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

        $this->prePersist($eventArgs)->shouldReturn(null);
    }
}
