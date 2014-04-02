<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

/**
 * An ArrayCollection decorator of entity identifiers that are lazy loaded
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    /**
     * @param string        $entityClass
     * @param array         $identifiers
     * @param EntityManager $entityManager
     */
    public function __construct($entityClass, $identifiers, EntityManager $entityManager)
    {
        $this->identifiers   = $identifiers;
        $this->entityClass   = $entityClass;
        $this->entityManager = $entityManager;
        $this->collection    = new ArrayCollection();
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
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->collection->clear();
        $this->setInitialized(false);
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
    }

    /**
     * Get object class identifier from the repository
     *
     * @return string
     */
    protected function getClassIdentifier()
    {
        $classMetadata = $this->entityManager->getClassMetadata($this->entityClass);

        return $classMetadata->getIdentifier();
    }
}
