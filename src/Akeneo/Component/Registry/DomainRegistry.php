<?php

namespace Akeneo\Component\Registry;

use Akeneo\Component\Registry\Exception\ExistingObjectException;
use Akeneo\Component\Registry\Exception\InvalidObjectException;
use Akeneo\Component\Registry\Exception\NonExistingObjectException;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DomainRegistry implements DomainRegistryInterface
{
    /** @var string */
    private $objectType;

    /** @var array */
    private $objects = [];
    /**
     * @param string $objectType
     */
    public function __construct($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * {@inheritdoc}
     */
    public function register($alias, $object)
    {
        if (!is_object($object) || !in_array($this->objectType, class_implements($object))) {
            throw new InvalidObjectException($this->objectType);
        }

        if ($this->has($alias)) {
            throw new ExistingObjectException($alias);
        }

        $this->objects[$alias] = $object;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->objects;
    }

    /**
     * {@inheritdoc}
     */
    public function has($alias)
    {
        return isset($this->objects[$alias]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($alias)
    {
        if (!$this->has($alias)) {
            throw new NonExistingObjectException($alias);
        }
        return $this->objects[$alias];
    }

    /**
     * {@inheritdoc}
     */
    public function unregister($alias)
    {
        if ($this->has($alias)) {
            unset($this->objects[$alias]);
        }
    }
}
