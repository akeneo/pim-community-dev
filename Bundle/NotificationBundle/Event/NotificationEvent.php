<?php

namespace Oro\Bundle\NotificationBundle\Event;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\Event;

class NotificationEvent extends Event
{
    /**
     * Event arguments
     *
     * @var
     */
    protected $args;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    public function __construct()
    {
        $this->args = func_get_args();
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

    /**
     * @param ObjectManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
