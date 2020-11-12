<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ClassificationBundle\Storage\Orm;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class CategorySaver extends BaseSaver
{
    private const LOCK_TTL = 10;

    private LockFactory $lockFactory;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $savedClass,
        LockFactory $lockFactory
    ) {
        parent::__construct($objectManager, $eventDispatcher, $savedClass);
        $this->lockFactory = $lockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        try {
            $lock = $this->lockUnitarySave($object);

            parent::save($object, $options);
        } finally {
            $lock->release();
        }
    }

    /**
     * There is a possible deadlock when creating several new categories at the same time.
     * Gedmo/Tree execute queries like `UPDATE pim_catalog_category SET lft = lft + 2 WHERE lft >= 2 AND root = ?`
     * producing GAP Lock issues.
     *
     * The entity manager is not capable to recover from such error.
     * By locking with an external lock beforehand, we can mitigate this deadlock issue.
     *
     * This lock is restricted to the category root, since only rows belonging to this root are affected by the
     * current queries.
     */
    private function lockUnitarySave(CategoryInterface $object): LockInterface
    {
        $lockIdentifier = sprintf('create_category_in_root_%d', $object->getRoot());
        $lock = $this->lockFactory->createLock($lockIdentifier, self::LOCK_TTL);

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
            throw new \LogicException('The lock for creating new categories cannot be acquired.');
        }

        return $lock;
    }
}
