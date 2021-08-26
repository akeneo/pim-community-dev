<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($objectManager, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement(BulkSaverInterface::class);
    }

    function it_saves_an_attribute_and_flushes_by_default(
        $objectManager,
        $eventDispatcher,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('my_code');

        $eventDispatcher->dispatch(
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent'),
            Argument::exact(StorageEvents::PRE_SAVE)
        )->shouldBeCalled();

        $objectManager->persist($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent'),
            Argument::exact(StorageEvents::POST_SAVE)
        )->shouldBeCalled();

        $this->save($attribute);
    }

    function it_saves_a_collection_attributes(
        $objectManager,
        $eventDispatcher,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $attribute1->getCode()->willReturn('my_code1');
        $attribute2->getCode()->willReturn('my_code2');

        $objectManager->persist($attribute1)->shouldBeCalled();
        $objectManager->persist($attribute2)->shouldBeCalled();

        $attributes = [
            $attribute1,
            $attribute2
        ];

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);

        $this->saveAll($attributes);

    }

    function it_throws_exception_when_save_anything_else_than_an_attribute()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Akeneo\Pim\Structure\Component\Model\AttributeInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
