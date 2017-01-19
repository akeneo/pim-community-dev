<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;

/**
 * Factory that creates a notification for contributors once the project is finished.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectFinishedNotificationFactory
{
    /** @var string */
    protected $notificationClass;

    /**
     * @param string $notificationClass
     */
    public function __construct($notificationClass)
    {
        $this->notificationClass = $notificationClass;
    }

    /**
     * @param string $message
     * @param array  $parameters
     *
     * @return NotificationInterface
     */
    public function create($message, $parameters)
    {
        $notification = new $this->notificationClass();

        $notification
            ->setType('success')
            ->setMessage($message)
            ->setMessageParams($parameters)
            ->setRoute('activity_manager_project_show')
            ->setRouteParams(['identifier' => $parameters['project_code']])
            ->setContext([
                'actionType'     => 'project_finished',
                'buttonLabel'    => sprintf('activity_manager.notification.%s.show', 'project_finished')
            ]);

        return $notification;
    }
}
