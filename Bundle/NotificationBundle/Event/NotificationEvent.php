<?php

namespace Oro\Bundle\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class NotificationEvent extends Event
{
    /**
     * @var mixed
     */
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Set entity
     *
     * @param $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
