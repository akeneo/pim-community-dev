<?php

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Exception\DuplicateObjectException;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BaseSaverSpec extends ObjectBehavior
{
    public function let(ObjectManager $objectManager, EventDispatcherInterface $eventDispatcher): void
    {
        $eventDispatcher->dispatch(Argument::any(), Argument::type('string'))->willReturn(Argument::type('object'));
        $this->beConstructedWith(
            $objectManager,
            $eventDispatcher,
            ModelToSave::class
        );
    }

    public function it_is_a_saver(): void
    {
        $this->shouldHaveType(SaverInterface::class);
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    public function it_persists_the_object_and_flushes_the_unit_of_work(ObjectManager $objectManager): void
    {
        $type = new ModelToSave();
        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->save($type);
    }

    public function it_persists_the_objects_and_flushes_the_unit_of_work(ObjectManager $objectManager): void
    {
        $type1 = new ModelToSave();
        $type2 = new ModelToSave();
        $objectManager->persist($type1)->shouldBeCalled();
        $objectManager->persist($type2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->saveAll([$type1, $type2]);
    }

    public function it_throws_exception_when_saving_anything_else_than_the_expected_class(): void
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

    public function it_dispatches_events_according_to_the_objects_state_on_unitary_save(EventDispatcherInterface $eventDispatcher): void
    {
        $newObject = new ModelToSave();
        $newObjectEvent = new GenericEvent($newObject, ['unitary' => true, 'is_new' => true]);

        $eventDispatcher->dispatch($newObjectEvent, StorageEvents::PRE_SAVE)->shouldBeCalled();
        $eventDispatcher->dispatch($newObjectEvent, StorageEvents::POST_SAVE)->shouldBeCalled();

        $this->save($newObject);

        $updatedObject = new ModelToSave(42);
        $updatedObjectEvent = new GenericEvent($updatedObject, ['unitary' => true, 'is_new' => false]);

        $eventDispatcher->dispatch($updatedObjectEvent, StorageEvents::PRE_SAVE)->shouldBeCalled();
        $eventDispatcher->dispatch($updatedObjectEvent, StorageEvents::POST_SAVE)->shouldBeCalled();

        $this->save($updatedObject);
    }

    public function it_dispatches_events_according_to_the_objects_state_on_bulk_save(EventDispatcherInterface $eventDispatcher): void
    {
        $newObject = new ModelToSave();
        $updatedObject = new ModelToSave(42);

        $bulkEvent = new GenericEvent([$newObject, $updatedObject], ['unitary' => false]);
        $newObjectEvent = new GenericEvent($newObject, ['unitary' => false, 'is_new' => true]);
        $updatedObjectEvent = new GenericEvent($updatedObject, ['unitary' => false, 'is_new' => false]);

        $eventDispatcher->dispatch($bulkEvent, StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch($newObjectEvent, StorageEvents::PRE_SAVE)->shouldBeCalled();
        $eventDispatcher->dispatch($updatedObjectEvent, StorageEvents::PRE_SAVE)->shouldBeCalled();
        $eventDispatcher->dispatch($newObjectEvent, StorageEvents::POST_SAVE)->shouldBeCalled();
        $eventDispatcher->dispatch($updatedObjectEvent, StorageEvents::POST_SAVE)->shouldBeCalled();
        $eventDispatcher->dispatch($bulkEvent, StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $this->saveAll([$newObject, $updatedObject]);
    }

    public function it_catches_orm_exception_and_throws_a_business_exception(ObjectManager $objectManager): void
    {
        $type = new ModelToSave();
        $objectManager->persist($type)->willThrow(UniqueConstraintViolationException::class);
        $objectManager->flush()->shouldNotBeCalled();

        $this->shouldThrow(DuplicateObjectException::class)->during('save', [$type]);
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
