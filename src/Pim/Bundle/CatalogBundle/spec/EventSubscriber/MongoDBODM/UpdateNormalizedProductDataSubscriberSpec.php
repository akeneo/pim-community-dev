<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\Console\CommandLauncher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\NormalizedDataQueryGeneratorInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Prophecy\Argument;

class UpdateNormalizedProductDataSubscriberSpec extends ObjectBehavior
{
    function let(
        NormalizedDataQueryGeneratorInterface $queryGenerator,
        CommandLauncher $launcher,
        $logFile
    ) {
        $this->beConstructedWith($launcher, $logFile);
        $this->addQueryGenerator($queryGenerator);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_some_product_events()
    {
        $this->getSubscribedEvents()->shouldReturn([Events::onFlush, Events::postFlush]);
    }

    function it_does_not_generate_query_if_no_update_or_delete_are_schedule(
        $queryGenerator,
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $uow
    ) {
        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($uow);

        $uow->getScheduledEntityUpdates()->willReturn([]);
        $uow->getScheduledEntityDeletions()->willReturn([]);
        $uow->getScheduledCollectionDeletions()->willReturn([]);
        $uow->getScheduledCollectionUpdates()->willReturn([]);

        $uow->getEntityChangeSet(null)->willReturn([]);

        $queryGenerator->supports()->shouldNotBeCalled();
        $queryGenerator->generateQuery()->shouldNotBeCalled();

        $this->onFlush($args);
    }

    function it_generates_query_if_entity_update_is_schedule(
        $queryGenerator,
        FamilyInterface $family,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $uow
    ) {
        $family->getAttributeAsLabel()->willReturn($attribute1);

        $family->setAttributeAsLabel($attribute2);

        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($uow);

        $uow->getScheduledEntityUpdates()->willReturn([$family]);
        $uow->getScheduledEntityDeletions()->willReturn([]);
        $uow->getScheduledCollectionDeletions()->willReturn([]);
        $uow->getScheduledCollectionUpdates()->willReturn([]);

        $uow->getEntityChangeSet($family)->willReturn(['attributeAsLabel' => [$attribute1, $attribute2]]);

        $queryGenerator->supports($family, 'attributeAsLabel')->willReturn(true);
        $queryGenerator->generateQuery($family, 'attributeAsLabel', $attribute1, $attribute2)->shouldBeCalled();

        $this->onFlush($args);
    }

    function it_generates_query_if_entity_delete_is_schedule(
        $queryGenerator,
        AttributeInterface $attribute,
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $uow
    ) {
        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($uow);

        $uow->getScheduledEntityUpdates()->willReturn([]);
        $uow->getScheduledEntityDeletions()->willReturn([$attribute]);
        $uow->getScheduledCollectionDeletions()->willReturn([]);
        $uow->getScheduledCollectionUpdates()->willReturn([]);

        $queryGenerator->supports($attribute, '')->willReturn(true);
        $queryGenerator->generateQuery($attribute, '', '', '')->shouldBeCalled();

        $this->onFlush($args);
    }

    function it_generates_query_if_collection_delete_is_schedule(
        $queryGenerator,
        AttributeOptionInterface $option,
        OnFlushEventArgs $args,
        EntityManagerInterface $em,
        UnitOfWork $uow
    ) {
        $optionCollection = new ArrayCollection([$option]);

        $args->getEntityManager()->willReturn($em);
        $em->getUnitOfWork()->willReturn($uow);

        $uow->getScheduledEntityUpdates()->willReturn([]);
        $uow->getScheduledEntityDeletions()->willReturn([]);
        $uow->getScheduledCollectionDeletions()->willReturn($optionCollection);
        $uow->getScheduledCollectionUpdates()->willReturn([]);

        $queryGenerator->supports($option, '')->willReturn(true);
        $queryGenerator->generateQuery($option, '', '', '')->shouldBeCalled();

        $this->onFlush($args);
    }
}
