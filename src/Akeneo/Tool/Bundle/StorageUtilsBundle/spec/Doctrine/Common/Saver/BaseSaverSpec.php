<?php

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BaseSaverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith(
            $objectManager,
            $eventDispatcher,
            ModelToSave::class
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType(SaverInterface::class);
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    function it_persists_the_object_and_flushes_the_unit_of_work($objectManager, $eventDispatcher)
    {
        $type = new ModelToSave();
        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->save($type);
    }

    function it_persists_the_objects_and_flushes_the_unit_of_work($objectManager, $eventDispatcher)
    {
        $type1 = new ModelToSave();
        $type2 = new ModelToSave();
        $objectManager->persist($type1)->shouldBeCalled();
        $objectManager->persist($type2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->saveAll([$type1, $type2]);
    }

    function it_throws_exception_when_saving_anything_else_than_the_expected_class()
    {
        $anythingElse = new ModelNotToSave();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "%s", "%s" provided.',
                ModelToSave::class,
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }

    function it_dispatches_events_according_to_the_objects_state_on_unitary_save($eventDispatcher)
    {
        $newObject = new ModelToSave();
        $newObjectEvent = new GenericEvent($newObject, ['unitary' => true, 'is_new' => true]);

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, $newObjectEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, $newObjectEvent)->shouldBeCalled();

        $this->save($newObject);

        $updatedObject = new ModelToSave(42);
        $updatedObjectEvent = new GenericEvent($updatedObject, ['unitary' => true, 'is_new' => false]);

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, $updatedObjectEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, $updatedObjectEvent)->shouldBeCalled();

        $this->save($updatedObject);
    }

    function it_dispatches_events_according_to_the_objects_state_on_bulk_save($eventDispatcher)
    {
        $newObject = new ModelToSave();
        $updatedObject = new ModelToSave(42);

        $bulkEvent = new GenericEvent([$newObject, $updatedObject], ['unitary' => false]);
        $newObjectEvent = new GenericEvent($newObject, ['unitary' => false, 'is_new' => true]);
        $updatedObjectEvent = new GenericEvent($updatedObject, ['unitary' => false, 'is_new' => false]);

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, $bulkEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, $newObjectEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, $updatedObjectEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, $newObjectEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, $updatedObjectEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, $bulkEvent)->shouldBeCalled();

        $this->saveAll([$newObject, $updatedObject]);
    }
}

class ModelToSave {
    private $id;

    public function __construct(?int $id = null) {
        $this->id = $id;
    }

    public function getId(): ?int {
        return $this->id;
    }
}

class ModelNotToSave {
    public function getId() {
        return 42;
    }
}
