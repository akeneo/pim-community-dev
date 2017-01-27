<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\Console\CommandLauncher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Prophecy\Argument;

class ProductRelatedEntityRemovalSubscriberSpec extends ObjectBehavior
{
    function let(CommandLauncher $launcher, $logFile)
    {
        $this->beConstructedWith($launcher, $logFile);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_some_product_events()
    {
        $this->getSubscribedEvents()->shouldReturn([Events::onFlush]);
    }

    function it_removes_related_entities_in_background_if_there_is_pending_updates(
        $launcher,
        $logFile,
        FamilyInterface $family1,
        FamilyInterface $family2,
        FamilyInterface $family3,
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $unitOfWork
    ) {
        $family1->getId()->willReturn(1);
        $family2->getId()->willReturn(2);
        $family3->getId()->willReturn(3);

        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getScheduledEntityDeletions()->willReturn([$family1, $family2, $family3]);

        $launcher->executeBackground('pim:product:remove-related-entity Family 1,2,3', $logFile)->shouldBeCalled();

        $this->onFlush($args);
    }

    function it_does_not_remove_related_entities_in_background_if_there_is_no_pending_updates(
        $launcher,
        $logFile,
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $unitOfWork
    ) {
        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getScheduledEntityDeletions()->willReturn([]);

        $launcher->executeBackground('pim:product:remove-related-entity', $logFile)->shouldNotBeCalled();

        $this->onFlush($args);
    }

    function it_throws_an_exeption_if_there_is_pending_updates_for_several_type_of_entity(
        FamilyInterface $family,
        AttributeInterface $attribute,
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $unitOfWork
    ) {
        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getScheduledEntityDeletions()->willReturn([$family, $attribute]);

        $this->shouldThrow('\InvalidArgumentException')->duringOnFlush($args);
    }
}
