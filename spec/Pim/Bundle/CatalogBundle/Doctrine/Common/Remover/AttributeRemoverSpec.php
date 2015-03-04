<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttributeRemoverSpec extends ObjectBehavior
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

    function it_dispatches_an_event_when_removing_an_attribute(
        $eventDispatcher,
        $objectManager,
        $optionsResolver,
        AttributeInterface $attribute
    ) {
        $optionsResolver->resolveRemoveOptions([])->willReturn(['flush' => true]);
        $eventDispatcher->dispatch(
            AttributeEvents::PRE_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            AttributeEvents::POST_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $this->remove($attribute);
    }

    function it_throws_exception_when_remove_anything_else_than_an_attribute()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects an "Pim\Bundle\CatalogBundle\Model\AttributeInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
