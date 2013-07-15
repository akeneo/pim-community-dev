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

    public function addHandler(EventHandlerInterface $handler)
    {
        $this->handlers[] = $handler;

    }

    public function getHandlers()
    {
        return $this->handlers;
    }
}