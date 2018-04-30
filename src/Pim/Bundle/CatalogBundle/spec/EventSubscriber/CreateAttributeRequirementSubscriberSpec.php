<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class CreateAttributeRequirementSubscriberSpec extends ObjectBehavior
{
    public function let(AttributeRequirementFactory $requirementFactory)
    {
        $this->beConstructedWith($requirementFactory);
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
        LifecycleEventArgs $eventArgs,
        EntityManagerInterface $entityManager
    ) {
        $eventArgs->getEntity()->willReturn(null);
        $eventArgs->getEntityManager()->shouldNotBeCalled();
        $entityManager->persist(Argument::any())->shouldNotBeCalled();

        $this->prePersist($eventArgs)->shouldReturn(null);
    }

    public function it_does_not_create_requirement_without_family(
        LifecycleEventArgs $eventArgs,
        ChannelInterface $channel,
        EntityManagerInterface $entityManager,
        EntityRepository $repository
    ) {
        $eventArgs->getEntity()->willReturn($channel);
        $eventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getRepository(FamilyInterface::class)->willReturn($repository);
        $repository->findAll()->willReturn([]);

        $entityManager->persist(Argument::any())->shouldNotBeCalled();
        $this->prePersist($eventArgs)->shouldReturn(null);
    }

    public function it_creates_requirements_on_identifier_attribute_for_each_family(
        $requirementFactory,
        LifecycleEventArgs $eventArgs,
        ChannelInterface $channel,
        EntityManagerInterface $entityManager,
        EntityRepository $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        FamilyInterface $family,
        FamilyInterface $otherFamily,
        AttributeInterface $identifier,
        AttributeRequirementInterface $attributeRequirement
    ) {
        $eventArgs->getEntity()->willReturn($channel);
        $eventArgs->getEntityManager()->willReturn($entityManager);

        $entityManager->getRepository(FamilyInterface::class)->willReturn($familyRepository);
        $familyRepository->findAll()->willReturn([$family, $otherFamily]);
        $entityManager->getRepository(AttributeInterface::class)->willReturn($attributeRepository);
        $attributeRepository->getIdentifier()->willReturn($identifier);

        $requirementFactory->createAttributeRequirement($identifier, $channel, true)
            ->willReturn($attributeRequirement)->shouldBeCalledTimes(2);
        $attributeRequirement->setFamily($family)->shouldBeCalled();
        $attributeRequirement->setFamily($otherFamily)->shouldBeCalled();
        $entityManager->persist($attributeRequirement)->shouldBeCalledTimes(2);

        $this->prePersist($eventArgs)->shouldReturn(null);
    }
}
