<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilySaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($objectManager, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    function it_saves_a_family($objectManager, $eventDispatcher, FamilyInterface $family)
    {
        $family->getCode()->willReturn('my_code');

        $objectManager->persist($family)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::cetera(), StorageEvents::PRE_SAVE)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::cetera(), StorageEvents::POST_SAVE)->shouldBeCalled();

        $this->save($family);
    }

    function it_saves_multiple_family(
        $objectManager,
        $eventDispatcher,
        FamilyInterface $family1,
        FamilyInterface $family2
    ) {
        $family1->getCode()->willReturn('my_code1');
        $family1->getCode()->willReturn('my_code2');

        $objectManager->persist($family1)->shouldBeCalled();
        $objectManager->persist($family2)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);

        $this->saveAll([$family1, $family2]);
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Akeneo\Pim\Structure\Component\Model\FamilyInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
