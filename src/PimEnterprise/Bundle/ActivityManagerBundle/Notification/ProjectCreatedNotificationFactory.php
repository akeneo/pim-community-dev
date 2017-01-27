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
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @param UserInterface    $user
     *
     * @return NotificationInterface
     */
    public function create(ProjectInterface $project, UserInterface $user)
    {
        $userLocale = $user->getUiLocale();
        $formattedDate = $this->datePresenter->present(
            $project->getDueDate(),
            ['locale' => $userLocale->getCode()]
        );

        $notification = new $this->notificationClass();

        $notification
            ->setType('success')
            ->setMessage('activity_manager.notification.message')
            ->setMessageParams(
                ['%project_label%' => $project->getLabel(), '%due_date%' => $formattedDate]
            )
            ->setRoute('activity_manager_project_show')
            ->setRouteParams(['identifier' => $project->getCode()])
            ->setContext([
                'actionType'     => 'project_calculation',
                'buttonLabel'    => sprintf('activity_manager.notification.%s.start', 'project_calculation')
            ]);

        return $notification;
    }
}
