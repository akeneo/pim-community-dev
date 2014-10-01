<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Event\AssociationTypeEvents;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssociationTypeManagerSpec extends ObjectBehavior
{
    function let(
        AssociationTypeRepository $repository,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($repository, $objectManager, $eventDispatcher);
    }

    function it_provides_all_association_types($repository)
    {
        $repository->findAll()->willReturn(['foo', 'bar']);

        $this->getAssociationTypes()->shouldReturn(['foo', 'bar']);
    }

    function it_dispatches_an_event_when_removing_an_association_type(
        $eventDispatcher,
        $objectManager,
        AssociationType $associationType
    ) {
        $eventDispatcher->dispatch(
            AssociationTypeEvents::PRE_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($associationType)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($associationType);
    }
}
