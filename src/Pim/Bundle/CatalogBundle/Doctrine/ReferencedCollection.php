<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * An ArrayCollection decorator of entity identifiers that are lazy loaded
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferencedCollection extends AbstractLazyCollection
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $entityClass;

    /** @var array */
    protected $identifiers;

    /** @var boolean */
    protected $initialized = false;

    /**
     * @param string        $entityClass
     * @param array         $identifiers
     * @param ObjectManager $objectManager
     */
    public function __construct($entityClass, $identifiers, ObjectManager $objectManager)
    {
        $this->identifiers   = $identifiers;
        $this->entityClass   = $entityClass;
        $this->objectManager = $objectManager;
        $this->collection    = new ArrayCollection();
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
                ->objectManager
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
        $classMetadata = $this->objectManager->getClassMetadata($this->entityClass);

        return $classMetadata->getIdentifier();
    }
}
