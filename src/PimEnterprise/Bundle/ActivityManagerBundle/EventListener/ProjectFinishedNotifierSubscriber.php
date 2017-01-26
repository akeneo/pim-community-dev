<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\NotificationChecker;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectFinishedNotificationFactory;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Job execution notifier.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectFinishedNotifierSubscriber implements EventSubscriberInterface
{
    /** @var ProjectFinishedNotificationFactory */
    protected $factory;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var NotificationChecker */
    protected $notificationChecker;

    /** @var ProjectStatusRepositoryInterface */
    protected $projectStatusRepository;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /** @var SaverInterface */
    protected $projectSaver;

    /**
     * @param ProjectFinishedNotificationFactory $factory
     * @param NotifierInterface                  $notifier
     * @param UserRepositoryInterface            $userRepository
     * @param NotificationChecker                $notificationChecker
     * @param ProjectStatusRepositoryInterface   $projectStatusRepository
     * @param ProjectRepositoryInterface         $projectRepository
     * @param SaverInterface                     $projectSaver
     */
    public function __construct(
        ProjectFinishedNotificationFactory $factory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        NotificationChecker $notificationChecker,
        ProjectStatusRepositoryInterface $projectStatusRepository,
        ProjectRepositoryInterface $projectRepository,
        SaverInterface $projectSaver
    ) {
        $this->factory = $factory;
        $this->notifier = $notifier;
        $this->userRepository = $userRepository;
        $this->notificationChecker = $notificationChecker;
        $this->projectStatusRepository = $projectStatusRepository;
        $this->projectRepository = $projectRepository;
        $this->projectSaver = $projectSaver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProjectEvents::PROJECT_CALCULATED => 'projectFinished',
        ];
    }

    /**
     * Notify a user when a project is finished.
     *
     * @param ProjectEvent $event
     */
    public function projectFinished(ProjectEvent $event)
    {
        $project = $event->getProject();

        $this->notifyOwner($project);
        $this->notifyContributors($project);
    }

    /**
     * @param ProjectInterface $project
     */
    protected function notifyOwner($project)
    {
        if (!$this->notificationChecker->isNotifiableForProjectFinished($project, $project->getOwner())) {
            return;
        }

        $this->setProjectCreated($project);
        $notification = $this->factory->create($project, 'activity_manager.notification.project_finished.owner');
        $this->notifier->notify($notification, [$project->getOwner()]);
        $this->projectStatusRepository->setProjectStatus($project, $project->getOwner(), true);
    }

    /**
     * @param ProjectInterface $project
     */
    protected function notifyContributors(ProjectInterface $project)
    {
        $contributors = $this->userRepository->findContributorsToNotify($project);
        foreach ($contributors as $contributor) {
            if ($this->notificationChecker->isNotifiableForProjectFinished($project, $contributor)) {
                $notification = $this->factory->create(
                    $project,
                    'activity_manager.notification.project_finished.contributor'
                );
                $this->notifier->notify($notification, [$contributor]);
                $this->projectStatusRepository->setProjectStatus($project, $contributor, true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setProjectCreated(ProjectInterface $project)
    {
        $project->setIsCreated(true);
        $this->projectSaver->save($project);
    }
}
