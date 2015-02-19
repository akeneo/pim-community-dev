<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Event\GroupEvents;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GroupRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($objectManager, $optionsResolver, $eventDispatcher);
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }

    function it_dispatches_an_event_when_removing_a_group(
        $eventDispatcher,
        $objectManager,
        $optionsResolver,
        GroupInterface $group
    ) {
        $optionsResolver->resolveRemoveOptions([])->willReturn(['flush' => true]);
        $eventDispatcher->dispatch(
            GroupEvents::PRE_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($group);
    }

    function it_throws_exception_when_remove_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects an "Pim\Bundle\CatalogBundle\Model\GroupInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
