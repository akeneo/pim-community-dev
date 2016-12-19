<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;

/**
 * Factory that creates a notification once the project is created. It notifies users that the project is ready to use.
 * They could click on it and they will be redirected to the filtered grid.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCreatedNotificationFactory
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
     * @param array $parameters
     *
     * @return NotificationInterface
     */
    public function create($parameters)
    {
        $notification = new $this->notificationClass();

        $notification
            ->setType('success')
            ->setMessage('activity_manager.notification.message')
            ->setMessageParams(
                ['%project_label%' => $parameters['project_label'], '%due_date%' => $parameters['due_date']]
            )
            ->setRoute('pim_enrich_product_index')
            ->setContext([
                'actionType' => 'project_calculation',
                'buttonLabel' => sprintf('activity_manager.notification.%s.start', 'project_calculation'),
                'gridParameters' => $parameters['filters'],
            ]);

        return $notification;
    }
}
