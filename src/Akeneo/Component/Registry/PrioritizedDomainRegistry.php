<?php

namespace Akeneo\Component\Registry;

use Akeneo\Component\Registry\Exception\InvalidObjectException;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrioritizedDomainRegistry implements PrioritizedDomainRegistryInterface
{
    /** @var string */
    private $objectType;

    /** @var array */
    private $objects;

    /** @var integer */
    private $queueOrder = PHP_INT_MAX;

    /**
     * @param string $objectType
     */
    public function __construct($objectType)
    {
        $this->objectType = $objectType;
        $this->objects = new \SplPriorityQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function register($priority, $object)
    {
        if (!is_object($object) || !in_array($this->objectType, class_implements($object))) {
            throw new InvalidObjectException($this->objectType);
        }

        $this->objects->insert($object, [$priority, $this->queueOrder--]);
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
    public function has($object)
    {
        foreach ($this->objects as $registeredObject) {
            if ($registeredObject === $object) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function unregister($object)
    {
        $recoverList= [];
        $this->objects->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

        foreach ($this->objects as $registeredObject) {
            if ($registeredObject['data'] !== $object) {
                $recoverList[] = $registeredObject;
            }
        }

        $this->objects->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

        foreach ($recoverList as $registeredObject) {
            $this->objects->insert($registeredObject['data'], $registeredObject['priority']);
        }
    }
}
