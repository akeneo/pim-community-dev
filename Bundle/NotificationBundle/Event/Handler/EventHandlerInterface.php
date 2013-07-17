<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Oro\Bundle\NotificationBundle\Event\NotificationEvent;

interface EventHandlerInterface
{
    /**
     * Handle event
     *
     * @param NotificationEvent $event
     * @return mixed
     */
    public function handle(NotificationEvent $event);
}
