<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Pim\Bundle\NotificationBundle\Entity\NotificationEvent;
use Pim\Bundle\NotificationBundle\Entity\Notification;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Notification factory
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationFactory
{
    /**
     * It creates a notification event
     *
     * @param string $message
     * @param string $type
     * @param array  $options
     *
     * @return NotificationEvent
     */
    public function createNotificationEvent($message, $type, array $options = [])
    {
        $defaults = [
            'messageParams' => [],
            'route' => null,
            'routeParams' => [],
            'context' => []
        ];

        $options = array_merge($defaults, $options);
        $event   = new NotificationEvent($message, $type);
        $event
            ->setMessageParams($options['messageParams'])
            ->setRoute($options['route'])
            ->setRouteParams($options['routeParams'])
            ->setContext($options['context']);

        return $event;
    }

    /**
     * It creates a notification
     *
     * @param NotificationEvent $event
     * @param User              $user
     *
     * @return Notification
     */
    public function createNotification(NotificationEvent $event, User $user)
    {
        return new Notification($event, $user);
    }
}
