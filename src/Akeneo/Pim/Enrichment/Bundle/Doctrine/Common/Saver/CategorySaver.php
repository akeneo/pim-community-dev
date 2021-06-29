<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class CategorySaver implements SaverInterface, BulkSaverInterface
{
    private const LOCK_TTL_IN_SECONDS = 10;

    private ObjectManager $objectManager;
    private EventDispatcherInterface $eventDispatcher;
    private LockFactory $lockFactory;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        LockFactory $lockFactory
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->lockFactory = $lockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        $this->validateObject($object);

        $lock = $this->lockUnitarySave($object);

        try {
            $options['unitary'] = true;
            $options['is_new'] = null === $object->getId();

            $this->eventDispatcher->dispatch(new GenericEvent($object, $options), StorageEvents::PRE_SAVE);

            $this->objectManager->persist($object);

            $this->objectManager->flush();

            $this->eventDispatcher->dispatch(new GenericEvent($object, $options), StorageEvents::POST_SAVE);
        } finally {
            $lock->release();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        if (empty($objects)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new GenericEvent($objects, $options), StorageEvents::PRE_SAVE_ALL);

        $areObjectsNew = array_map(function ($object) {
            return null === $object->getId();
        }, $objects);

        foreach ($objects as $i => $object) {
            $this->validateObject($object);

            $this->eventDispatcher->dispatch(
                new GenericEvent($object, array_merge($options, ['is_new' => $areObjectsNew[$i]])),
                StorageEvents::PRE_SAVE
            );

            $this->objectManager->persist($object);
        }

        $this->objectManager->flush();

        foreach ($objects as $i => $object) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($object, array_merge($options, ['is_new' => $areObjectsNew[$i]])),
                StorageEvents::POST_SAVE
            );
        }

        $this->eventDispatcher->dispatch(new GenericEvent($objects, $options), StorageEvents::POST_SAVE_ALL);
    }

    protected function validateObject($object)
    {
        if (!$object instanceof CategoryInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    CategoryInterface::class,
                    ClassUtils::getClass($object)
                )
            );
        }
    }

    /**
     * There is a possible deadlock when creating several new categories at the same time.
     * Gedmo/Tree execute queries like `UPDATE pim_catalog_category SET lft = lft + 2 WHERE lft >= 2 AND root = ?`
     * producing GAP Lock issues.
     *
     * The entity manager is not capable to recover from such error and is automatically closed.
     * A new entity manager could be created but when a new one is created using the ManagerRegistry, there is a lot of
     * issues because the reference to the previous entity manager is kept in many places.
     *
     * By locking with an external lock beforehand, we can mitigate this deadlock issue.
     *
     * This lock is restricted to the category root, since only rows belonging to this root are affected by the
     * current queries.
     */
    private function lockUnitarySave(CategoryInterface $object): LockInterface
    {
        $lockIdentifier = sprintf('create_category_in_root_%d', $object->getRoot());
        $lock = $this->lockFactory->createLock($lockIdentifier, self::LOCK_TTL_IN_SECONDS);

        $errors = 0;
        $acquired = false;

        while (!$acquired && $errors < 3) {
            try {
                $acquired = $lock->acquire(true);
            } catch (LockConflictedException $ex) {
                $errors++;
                continue;
            }
        }

        if (!$acquired) {
            throw new \ErrorException('The lock for creating new categories cannot be acquired.');
        }

        return $lock;
    }
}
