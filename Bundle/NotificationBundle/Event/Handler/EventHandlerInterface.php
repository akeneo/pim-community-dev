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

    /**
     * Handle event
     *
     * @return mixed
     */
    public function handle();
}
