<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Collections;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * An ArrayCollection decorator of entity identifiers that are lazy loaded
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber
 */
class ReferencedCollection extends AbstractLazyCollection
{
    /** @var string */
    protected $entityClass;

    /** @var array */
    protected $identifiers;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var bool */
    protected $initialized = false;

    /** @var ObjectManager */
    protected $manager;

    /** @var object|null */
    protected $owner;

    /**
     * @param string          $entityClass
     * @param array           $identifiers
     * @param ManagerRegistry $registry
     */
    public function __construct($entityClass, $identifiers, ManagerRegistry $registry)
    {
        $this->identifiers = $identifiers;
        $this->entityClass = $entityClass;
        $this->registry = $registry;
        $this->collection = new ArrayCollection();
    }

    /**
     * Set collection owner
     *
     * @param object $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Sets the initialized flag of the collection, forcing it into that state.
     *
     * @param bool $bool
     */
    public function setInitialized($bool)
    {
        $this->initialized = $bool;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->initialize();
        $remove = $this->collection->remove($key);
        if ($remove) {
            $this->changed();
        }

        return $remove;
    }

    /**
     * {@inheritdoc}
     */
    public function removeElement($element)
    {
        $this->initialize();
        $remove = $this->collection->removeElement($element);
        if ($remove) {
            $this->changed();
        }

        return $remove;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->initialize();
        $this->changed();

        $this->collection->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function add($element)
    {
        $this->initialize();
        $this->collection->add($element);
        $this->changed();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->initialize();
        $this->changed();
        $this->collection->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        if (!$this->initialized) {
            $this->doInitialize();
            $this->initialized = true;
        }
    }

    /**
     * Populate the collection
     *
     * @param Collection $collection
     */
    public function populate(Collection $collection)
    {
        $this->collection = $collection;
        $this->initialized = true;
        $this->changed();
    }

    /**
     * Initializes the collection by loading its contents from the database
     * if the collection is not yet initialized.
     */
    protected function doInitialize()
    {
        if (empty($this->identifiers) || empty($this->entityClass)) {
            return;
        }

        $classIdentifier = $this->getClassIdentifier();
        if (count($classIdentifier) > 1) {
            throw new \LogicException(
                'The configured entity uses a composite key which is not supported by the collection'
            );
        }

        $this->collection = new ArrayCollection(
            $this
                ->registry
                ->getRepository($this->entityClass)
                ->findBy([$classIdentifier[0] => $this->identifiers])
        );
    }

    /**
     * Get object class identifiers from the repository
     *
     * @return array
     */
    protected function getClassIdentifier()
    {
        $classMetadata = $this->registry->getManagerForClass($this->entityClass)->getClassMetadata($this->entityClass);

        return $classMetadata->getIdentifier();
    }

    /**
     * Schedule update of the owner in the unit of work
     */
    private function changed()
    {
        if (null === $this->manager) {
            $this->manager = $this->registry->getManagerForClass(ClassUtils::getClass($this->owner));
        }

        $uow = $this->manager->getUnitOfWork();
        if (UnitOfWork::STATE_MANAGED === $uow->getDocumentState($this->owner)
            && !$uow->isScheduledForUpdate($this->owner)
        ) {
            $uow->scheduleForUpdate($this->owner);
        }
    }
}
