<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Event\AttributeOptionEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttributeOptionRemoverSpec extends ObjectBehavior
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

    function it_dispatches_an_event_when_removing_an_attribute_option(
        $eventDispatcher,
        $objectManager,
        $optionsResolver,
        AttributeOptionInterface $option
    ) {
        $optionsResolver->resolveRemoveOptions([])->willReturn(['flush' => true]);
        $eventDispatcher->dispatch(
            AttributeOptionEvents::PRE_REMOVE,
            Argument::type('Akeneo\Component\StorageUtils\Event\RemoveEvent')
        )->shouldBeCalled();

        $objectManager->remove($option)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            AttributeOptionEvents::POST_REMOVE,
            Argument::type('Akeneo\Component\StorageUtils\Event\RemoveEvent')
        )->shouldBeCalled();

        $this->remove($option);
    }

    function it_throws_exception_when_remove_anything_else_than_a_option()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects an "Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
