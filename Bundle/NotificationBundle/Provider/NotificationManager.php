<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface;

class NotificationManager
{
    /**
     * @var EventHandlerInterface[] handlers
     */
    protected $handlers;

    public function __construct()
    {
        $this->handlers = array();
    }

    /**
     * Add handler to list
     *
     * @param EventHandlerInterface $handler
     */
    public function addHandler(EventHandlerInterface $handler)
    {
        $this->handlers[] = $handler;

    }

    /**
     * Return list of handlers
     *
     * @return EventHandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }
}
