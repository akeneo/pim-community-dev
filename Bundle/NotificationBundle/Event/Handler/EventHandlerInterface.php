<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Symfony\Component\HttpFoundation\ParameterBag;

interface EventHandlerInterface
{
    /**
     * Handle event
     *
     * @param NotificationEvent $event
     * @param EmailNotification[] $matchedNotifications
     * @return mixed
     */
    public function handle(NotificationEvent $event, $matchedNotifications);

    /**
     * Process with actual notification
     *
     * @param ParameterBag $params
     * @return mixed
     */
    public function notify(ParameterBag $params);
}
