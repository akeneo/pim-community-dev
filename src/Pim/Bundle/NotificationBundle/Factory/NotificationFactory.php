<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Pim\Bundle\NotificationBundle\Entity\Notification;

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
     * It creates a notification
     *
     * @param string $message
     * @param string $type
     * @param array  $options
     *
     * @return Notification
     */
    public function createNotification($message, $type, array $options = [])
    {
        $defaults = [
            'messageParams' => [],
            'route' => null,
            'routeParams' => [],
            'context' => []
        ];

        $options      = array_merge($defaults, $options);
        $notification = new Notification($message, $type);
        $notification
            ->setMessageParams($options['messageParams'])
            ->setRoute($options['route'])
            ->setRouteParams($options['routeParams'])
            ->setContext($options['context']);

        return $notification;
    }
}
