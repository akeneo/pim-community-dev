<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Factory;

/**
 * Factory that creates a notification for project calculation from a job instance
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
     * {@inheritdoc}
     */
    public function create()
    {
        $notification = new $this->notificationClass();

        $notification
            ->setType('success')
            ->setMessage('Project ready baby !!!!!!!!!!!!!!!')
            ->setMessageParams(['%label%' => ''])
            ->setRoute('pim_enrich_product_index')
            ->setContext([
                'actionType' => 'project_calculation',
                'buttonLabel' => sprintf('activity_manager.notification.%s.start', 'project_calculation'),
            ]);

        return $notification;
    }
}
