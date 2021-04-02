<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Lock\Query\EnsureLockTableExistsInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class CategorySaverSpec extends ObjectBehavior
{
    public function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        LockFactory $lockFactory,
        EnsureLockTableExistsInterface $ensureLockTableExists
    ): void {
        $this->beConstructedWith($objectManager, $eventDispatcher, $lockFactory, $ensureLockTableExists);
    }

    public function it_is_a_saver(): void
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    public function it_saves_a_category(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        LockFactory $lockFactory,
        LockInterface $lock,
        CategoryInterface $category,
        EnsureLockTableExistsInterface $ensureLockTableExists
    ): void {
        $category->getId()->willReturn(null);
        $category->getRoot()->willReturn(1);

        $ensureLockTableExists->execute()->shouldBeCalled();
        $lockFactory
            ->createLock('create_category_in_root_1', 10)
            ->willReturn($lock);

        $lock->acquire(true)->willReturn(true);

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::PRE_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof CategoryInterface
                        && $event->getArgument('unitary') === true;
                }
            )
        )->shouldBeCalled();

        $objectManager->persist($category)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::POST_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof CategoryInterface
                        && $event->getArgument('unitary') === true;
                }
            )
        )->shouldBeCalled();

        $lock->release()->shouldBeCalled();

        $this->save($category);
    }

    public function it_throws_if_the_lock_cannot_be_acquired(
        LockFactory $lockFactory,
        LockInterface $lock,
        CategoryInterface $category,
        EnsureLockTableExistsInterface $ensureLockTableExists
    ) {
        $category->getId()->willReturn(null);
        $category->getRoot()->willReturn(1);

        $ensureLockTableExists->execute()->shouldBeCalled();
        $lockFactory
            ->createLock('create_category_in_root_1', 10)
            ->willReturn($lock);

        $lock->acquire(true)->willThrow(LockConflictedException::class);

        $this->shouldThrow(\ErrorException::class)
            ->during('save', [$category]);
    }
}
