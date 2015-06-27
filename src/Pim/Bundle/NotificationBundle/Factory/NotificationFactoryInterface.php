<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;


/**
 * Notification factory interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NotificationFactoryInterface
{
    /**
     * Creates a notification
     *
     * @param string $message
     * @param string $type
     * @param array  $options
     *
     * @return NotificationInterface
     */
    public function createNotification($message, $type, array $options = []);
}