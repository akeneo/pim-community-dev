<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Event\AssociationTypeEvents;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssociationTypeManagerSpec extends ObjectBehavior
{
    function let(
        AssociationTypeRepositoryInterface $repository,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($repository, $objectManager, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Akeneo\Component\Persistence\SaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\Persistence\RemoverInterface');
    }

    function it_provides_all_association_types($repository)
    {
        $repository->findAll()->willReturn(['foo', 'bar']);

        $this->getAssociationTypes()->shouldReturn(['foo', 'bar']);
    }

    function it_dispatches_an_event_when_removing_an_association_type(
        $eventDispatcher,
        $objectManager,
        AssociationTypeInterface $associationType
    ) {
        $eventDispatcher->dispatch(
            AssociationTypeEvents::PRE_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($associationType)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($associationType);
    }

    function it_throws_exception_when_save_anything_else_than_an_assoccation_type()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects an "Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface", "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringSave($anythingElse);
    }

    function it_throws_exception_when_remove_anything_else_than_an_assocation_type()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects an "Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface", "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
