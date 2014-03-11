<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

use \Closure;

/**
 * An ArrayCollection decorator of entity identifiers that are lazy loaded
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferencedCollection implements Collection
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $entityClass;

    /** @var array */
    protected $identifiers;

    /** @var ArrayCollection */
    protected $entities;

    /** @var boolean */
    protected $initialized = true;

    /**
     * @param string        $entityClass
     * @param array         $identifiers
     * @param ObjectManager $objectManager
     */
    public function __construct($entityClass, $identifiers, ObjectManager $objectManager)
    {
        $this->initialized   = false;
        $this->identifiers   = $identifiers;
        $this->entityClass   = $entityClass;
        $this->objectManager = $objectManager;
        $this->items         = new ArrayCollection();
    }

    /**
     * Sets the initialized flag of the collection, forcing it into that state.
     *
     * @param boolean $bool
     *
     * @return void
     */
    public function setInitialized($bool)
    {
        $this->initialized = $bool;
    }

    /**
     * Checks whether this collection has been initialized.
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * {@inheritdoc}
     */
    public function add($element)
    {
        $this->initialize();

        return $this->items->add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->items->clear();
        $this->setInitialized(false);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element)
    {
        $this->initialize();

        return $this->items->contains($element);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        $this->initialize();

        return $this->items->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->initialize();

        return $this->items->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function removeElement($element)
    {
        $this->initialize();

        return $this->items->removeElement($element);
    }

    /**
     * {@inheritdoc}
     */
    public function containsKey($key)
    {
        $this->initialize();

        return $this->items->containKeys($key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $this->initialize();

        return $this->items->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        $this->initialize();

        return $this->items->getKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        $this->initialize();

        return $this->items->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->initialize();

        $this->items->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $this->initialize();

        return $this->items->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        $this->initialize();

        return $this->items->first();
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        $this->initialize();

        return $this->items->last();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $this->initialize();

        return $this->items->key();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->initialize();

        return $this->items->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->initialize();

        return $this->items->next();
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Closure $predicate)
    {
        $this->initialize();

        return $this->items->exists($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(Closure $predicate)
    {
        $this->initialize();

        return $this->items->filter($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function forAll(Closure $predicate)
    {
        $this->initialize();

        return $this->items->forAll($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function map(Closure $predicate)
    {
        $this->initialize();

        return $this->items->map($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function partition(Closure $predicate)
    {
        $this->initialize();

        return $this->items->partition($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($element)
    {
        $this->initialize();

        return $this->items->indexOf($element);
    }

    /**
     * {@inheritdoc}
     */
    public function slice($offset, $length = null)
    {
        $this->initialize();

        return $this->items->slice($offset, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->initialize();

        return $this->items->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $this->initialize();

        return $this->items->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $this->initialize();

        return $this->items->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->initialize();

        return $this->items->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->initialize();

        return $this->items->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->initialize();

        return $this->items->offsetSet($offset);
    }

    /**
     * Initializes the collection by loading its contents from the database
     * if the collection is not yet initialized.
     *
     * @return void
     */
    protected function initialize()
    {
        if ($this->initialized || empty($this->identifiers) || empty($this->entityClass)) {
            return;
        }

        $classIdentifier = $this->getClassIdentifier();
        if (count($classIdentifier) > 1) {
            throw new \LogicException('The configured entity uses a composite key which is not supported by the collection');
        }

        $this->initialized = true;
        $this->items       = new ArrayCollection(
            $this
                ->objectManager
                ->getRepository($this->entityClass)
                ->findBy([$classIdentifier[0] => $this->identifiers])
        );
    }

    /**
     * Get object class identifier from the repository
     *
     * @param mixed $itemRepository
     *
     * @return string
     */
    protected function getClassIdentifier()
    {
        $classMetadata = $this->objectManager->getClassMetadata($this->entityClass);

        return $classMetadata->getIdentifier();
    }

}
