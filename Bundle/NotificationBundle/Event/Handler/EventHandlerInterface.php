<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

interface EventHandlerInterface
{
    /**
     * Check if handler can handle event
     *
     * @return bool
     */
    public function shouldHandle();
}