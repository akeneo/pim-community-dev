<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\EventListener;

use Akeneo\ActivityManager\Bundle\Notification\ProjectCreatedNotificationFactory;
use Akeneo\ActivityManager\Component\Event\ProjectEvent;
use Akeneo\ActivityManager\Component\Event\ProjectEvents;
use Akeneo\ActivityManager\Component\Repository\UserRepositoryInterface;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Job execution notifier.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCreationNotifierSubscriber implements EventSubscriberInterface
{
    /** @var ProjectCreatedNotificationFactory */
    private $factory;

    /** @var NotifierInterface */
    private $notifier;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var PresenterInterface */
    private $datePresenter;

    /**
     * @param ProjectCreatedNotificationFactory $factory
     * @param NotifierInterface                 $notifier
     * @param UserRepositoryInterface           $userRepository
     * @param PresenterInterface                $datePresenter
     */
    public function __construct(
        ProjectCreatedNotificationFactory $factory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        PresenterInterface $datePresenter
    ) {
        $this->factory = $factory;
        $this->notifier = $notifier;
        $this->userRepository = $userRepository;
        $this->datePresenter = $datePresenter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProjectEvents::PROJECT_CALCULATED => 'projectCreated',
        ];
    }

    /**
     * Notify a user when a project is created.
     *
     * @param ProjectEvent $event
     */
    public function projectCreated(ProjectEvent $event)
    {
        $project = $event->getProject();

        $view = $project->getDatagridView();
        $filters = $view->getFilters();

        $users = $this->userRepository->findContributorsToNotify($project);

        foreach ($users as $user) {
            $userLocale = $user->getUiLocale();
            $formattedDate = $this->datePresenter->present(
                $project->getDueDate(),
                ['locale' => $userLocale->getCode()]
            );

            $parameters['due_date'] = $formattedDate;
            $parameters['project_label'] = $project->getLabel();
            $parameters['filters'] = $filters;

            $notification = $this->factory->create($parameters);
            $this->notifier->notify($notification, [$user]);
        }
    }
}
