<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamWorkAssistantBundle\Notification;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;

/**
 * Factory that creates a notification.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectNotificationFactory
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
     * @param array  $routeParams
     * @param array  $parameters
     * @param array  $context
     * @param string $message
     *
     * @return NotificationInterface
     */
    public function create(array $routeParams, array $parameters, array $context, $message)
    {
        $notification = new $this->notificationClass();

        $notification
            ->setType('success')
            ->setMessage($message)
            ->setMessageParams($parameters)
            ->setRoute('team_work_assistant_project_show')
            ->setRouteParams($routeParams)
            ->setContext($context);

        return $notification;
    }
}
