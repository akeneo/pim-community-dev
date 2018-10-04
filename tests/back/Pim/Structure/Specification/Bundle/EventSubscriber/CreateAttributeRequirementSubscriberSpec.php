<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\Family;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
    public function let(
        AttributeRequirementFactory $requirementFactory,
        LifecycleEventArgs $eventArgs,
        ChannelInterface $channel,
        EntityManagerInterface $entityManager,
        EntityRepository $repository,
        FamilyInterface $family
    ) {
        $this->beConstructedWith($requirementFactory);

        $eventArgs->getEntity()
            ->willReturn($channel);

        $eventArgs->getEntityManager()
            ->willReturn($entityManager);

        $entityManager->getRepository(Argument::exact(Family::class))
            ->willReturn($repository);

        $repository->findAll()
            ->willReturn([$family]);

        $family->getAttributes()
            ->willReturn([]);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    public function it_subscribes_to_prePersist()
    {
        $this->getSubscribedEvents()
            ->shouldReturn(['prePersist']);
    }

    public function it_ignores_non_ChannelInterface_entity(
        $eventArgs,
        $entityManager
    ) {
        $eventArgs->getEntity()
            ->willReturn(null)
            ->shouldBeCalled();

        $eventArgs->getEntityManager()
            ->shouldNotBeCalled();

        $entityManager->persist(Argument::any())
            ->shouldNotBeCalled();

        $this->prePersist($eventArgs)
            ->shouldReturn(null);
    }

    public function it_does_not_create_requirement_without_family(
        $eventArgs,
        $entityManager,
        $repository,
        $family
    ) {
        $eventArgs->getEntityManager()
            ->shouldBeCalled();

        $entityManager->getRepository(Argument::exact(Family::class))
            ->shouldBeCalled();

        $repository->findAll()
            ->willReturn([])
            ->shouldBeCalled();

        $family->getAttributes()
            ->shouldNotBeCalled();

        $entityManager->persist(Argument::any())
            ->shouldNotBeCalled();

        $entityManager->persist(Argument::any())
            ->shouldNotBeCalled();

        $this->prePersist($eventArgs)
            ->shouldReturn(null);
    }

    public function it_does_not_create_requirements_for_family_without_attributes(
        $eventArgs,
        $entityManager,
        $repository,
        $family
    ) {
        $repository->findAll()
            ->willReturn([$family])
            ->shouldBeCalled();

        $family->getAttributes()
            ->willReturn([])
            ->shouldBeCalled();

        $entityManager->persist(Argument::any())
            ->shouldNotBeCalled();

        $this->prePersist($eventArgs)
            ->shouldReturn(null);
    }

    public function it_creates_requirements(
        $requirementFactory,
        $eventArgs,
        $channel,
        $entityManager,
        $family,
        AttributeInterface $attribute,
        AttributeRequirementInterface $attributeRequirement
    ) {
        $family->getAttributes()
            ->willReturn([$attribute])
            ->shouldBeCalled();

        $requirementFactory->createAttributeRequirement(
            $attribute,
            $channel,
            Argument::type('bool')
        )
            ->willReturn($attributeRequirement)
            ->shouldBeCalled();

        $attributeRequirement->setFamily($family)
            ->shouldBeCalled();

        $entityManager->persist(Argument::any())
            ->shouldBeCalled();

        $this->prePersist($eventArgs)
            ->shouldReturn(null);
    }
}
