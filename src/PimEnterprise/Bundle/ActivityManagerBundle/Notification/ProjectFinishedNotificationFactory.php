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

use Akeneo\Component\Localization\Presenter\DatePresenter;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * Factory that creates a notification for contributors once the project is finished.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectFinishedNotificationFactory
{
    /** @var string */
    protected $notificationClass;

    /** @var DatePresenter */
    protected $datePresenter;

    /**
     * @param DatePresenter $datePresenter
     * @param string        $notificationClass
     */
    public function __construct(DatePresenter $datePresenter, $notificationClass)
    {
        $this->notificationClass = $notificationClass;
        $this->datePresenter = $datePresenter;
    }

    /**
     * @param ProjectInterface $project
     * @param string           $message
     *
     * @return NotificationInterface
     */
    public function create(ProjectInterface $project, $message)
    {
        $userLocale = $project->getOwner()->getUiLocale();
        $formattedDate = $this->datePresenter->present(
            $project->getDueDate(),
            ['locale' => $userLocale->getCode()]
        );

        $parameters = [
            '%project_label%' => '"' . $project->getLabel() . '"',
            '%due_date%' => '"' . $formattedDate . '"',
            'project_code' => $project->getCode(),
        ];

        $notification = new $this->notificationClass();

        $notification
            ->setType('success')
            ->setMessage($message)
            ->setMessageParams($parameters)
            ->setRoute('activity_manager_project_show')
            ->setRouteParams(['identifier' => $parameters['project_code']])
            ->setContext([
                'actionType'  => 'project_finished',
                'buttonLabel' => 'activity_manager.notification.project_finished.show',
            ]);

        return $notification;
    }
}
