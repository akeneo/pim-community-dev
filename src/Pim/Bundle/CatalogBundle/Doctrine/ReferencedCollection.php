<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

use \Closure;

/**
 * Provides a collection lazy loaded from the object manager.
 * The content of the collection is defined by the ids and
 * the class name.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferencedCollection implements Collection
{
    /**
     * Object manager that will be used to get items from storage
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Class name of item to load from storage
     *
     * @var string
     */
    protected $itemClass;

    /**
     * Array of item ids
     *
     * @var array
     */
    protected $itemIds;

    /**
     * Array of items
     *
     * @var ArrayCollection
     */
    protected $items;

    /**
     * Whether the collection has already been initialized.
     *
     * @var boolean
     */
    protected $initialized = true;

    /**
     * Constructor
     *
     * @param string        $itemClass
     * @param array         $itemIds
     * @param ObjectManager $objectManager
     */
    public function __construct($itemClass, $itemIds, ObjectManager $objectManager)
    {
        $this->initialized   = false;
        $this->items         = new ArrayCollection();
        $this->itemClass     = $itemClass;
        $this->itemIds       = $itemIds;
        $this->objectManager = $objectManager;
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
     * Get object class identifier from the repository
     *
     * @param mixed $itemRepository
     *
     * @return string
     */
    protected function getClassIdentifier()
    {
        $classMetadata = $this->objectManager->getClassMetadata($this->itemClass);
        return $classMetadata->getIdentifier();
    }

    /**
     * Initializes the collection by loading its contents from the database
     * if the collection is not yet initialized.
     *
     * @return void
     */
    public function initialize()
    {
        if ($this->initialized || empty($this->itemIds) || empty($this->itemClass)) {
            return;
        }
        $itemRepository = $this->objectManager->getRepository($this->itemClass);
        $criteria = array($this->getClassIdentifier() => $this->itemIds);

        $this->items = new ArrayCollection($itemRepository->findBy($criteria));

        $this->initialized = true;
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
    public function exists(Closure $p)
    {
        $this->initialize(); 
        return $this->items->exists($p);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(Closure $p)
    {
        $this->initialize(); 
        return $this->items->filter($p);
    }

    /**
     * {@inheritdoc}
     */
    public function forAll(Closure $p)
    {
        $this->initialize(); 
        return $this->items->forAll($p);
    }

    /**
     * {@inheritdoc}
     */
    public function map(Closure $p)
    {
        $this->initialize(); 
        return $this->items->map($p);
    }

    /**
     * {@inheritdoc}
     */
    public function partition(Closure $p)
    {
        $this->initialize(); 
        return $this->items->partition($p);
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
} 
