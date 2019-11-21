<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\Family;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
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
        Connection $dbConnection,
        LifecycleEventArgs $eventArgs,
        ChannelInterface $channel,
        EntityManagerInterface $entityManager,
        EntityRepository $repository,
        FamilyInterface $family
    ) {
        $this->beConstructedWith($requirementFactory, $dbConnection);

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

        $channel->getId()->willReturn(7);
        $family->getId()->willReturn(23);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    public function it_subscribes_to_prePersist()
    {
        $this->getSubscribedEvents()
            ->shouldReturn([Events::postPersist]);
    }

    public function it_ignores_non_ChannelInterface_entity(
        $eventArgs,
        $entityManager,
        $dbConnection
    ) {
        $eventArgs->getEntity()
            ->willReturn(null)
            ->shouldBeCalled();

        $eventArgs->getEntityManager()
            ->shouldNotBeCalled();

        $entityManager->getRepository(Argument::any())
            ->shouldNotBeCalled();

        $dbConnection->executeQuery(Argument::any())
            ->shouldNotBeCalled();

        $this->postPersist($eventArgs)
            ->shouldReturn(null);
    }

    public function it_does_not_create_requirement_without_family(
        $eventArgs,
        $entityManager,
        $dbConnection,
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

        $dbConnection->executeQuery(Argument::any())
            ->shouldNotBeCalled();

        $this->postPersist($eventArgs)
            ->shouldReturn(null);
    }

    public function it_does_not_create_requirements_for_family_without_attributes(
        $eventArgs,
        $dbConnection,
        $repository,
        $family
    ) {
        $repository->findAll()
            ->willReturn([$family])
            ->shouldBeCalled();

        $family->getAttributes()
            ->willReturn([])
            ->shouldBeCalled();

        $dbConnection->executeQuery(Argument::any())
            ->shouldNotBeCalled();

        $this->postPersist($eventArgs)
            ->shouldReturn(null);
    }

    public function it_creates_requirements(
        $requirementFactory,
        $eventArgs,
        $channel,
        $dbConnection,
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

        $attributeRequirement->getChannel()->willReturn($channel);
        $attributeRequirement->getAttribute()->willReturn($attribute);
        $attributeRequirement->getFamily()->willReturn($family);
        $attributeRequirement->isRequired()->willReturn(false);

        $dbConnection->executeQuery(Argument::any())
            ->shouldBeCalled();

        $this->postPersist($eventArgs)
            ->shouldReturn(null);
    }
}
