<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * An ArrayCollection decorator of entity identifiers that are lazy loaded
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Pim\Bundle\CatalogBundle\EventListener\MongoDBODM\EntitiesTypeSubscriber
 */
class ReferencedCollection extends AbstractLazyCollection
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var string */
    protected $entityClass;

    /** @var array */
    protected $identifiers;

    /** @var boolean */
    protected $initialized = false;

    /** @var boolean */
    protected $isDirty = false;

    /** @var UnitOfWork */
    protected $uow;

    /** @var object|null */
    protected $owner;

    /**
     * @param string        $entityClass
     * @param array         $identifiers
     * @param EntityManager $entityManager
     * @param UnitOfWork    $uow
     */
    public function __construct($entityClass, $identifiers, EntityManager $entityManager, UnitOfWork $uow)
    {
        $this->identifiers   = $identifiers;
        $this->entityClass   = $entityClass;
        $this->entityManager = $entityManager;
        $this->uow           = $uow;
        $this->collection    = new ArrayCollection();
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
     * @param boolean $bool
     *
     * @return null
     */
    public function setInitialized($bool)
    {
        $this->initialized = $bool;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        $this->initialize();
        $this->changed();

        $this->collection->set($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function add($element)
    {
        $this->initialize();
        $this->collection->add($element);
        $this->changed();

        return true;
    }

    /**
     * {@inheritDoc}
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
     * Initializes the collection by loading its contents from the database
     * if the collection is not yet initialized.
     *
     * @return null
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
                ->entityManager
                ->getRepository($this->entityClass)
                ->findBy([$classIdentifier[0] => $this->identifiers])
        );
        $this->changed();
    }

    /**
     * Get object class identifiers from the repository
     *
     * @return array
     */
    protected function getClassIdentifier()
    {
        $classMetadata = $this->entityManager->getClassMetadata($this->entityClass);

        return $classMetadata->getIdentifier();
    }

    /**
     * Schedule update of the owner in the unit of work
     */
    private function changed()
    {
        if ($this->isDirty) {
            return;
        }

        $this->isDirty = true;
        $this->uow->scheduleForUpdate($this->owner);
    }
}
