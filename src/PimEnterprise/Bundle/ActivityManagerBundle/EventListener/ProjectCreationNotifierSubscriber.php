<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\NotificationChecker;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
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
class ProjectCreationNotifierSubscriber implements EventSubscriberInterface
{
    /** @var ProjectCreatedNotificationFactory */
    protected $notificationFactory;

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
     * @param ProjectCreatedNotificationFactory $notificationFactory
     * @param NotifierInterface                 $notifier
     * @param UserRepositoryInterface           $userRepository
     * @param NotificationChecker               $notificationChecker
     * @param ProjectStatusRepositoryInterface  $projectStatusRepository
     * @param ProjectRepositoryInterface        $projectRepository
     * @param SaverInterface                    $projectSaver
     */
    public function __construct(
        ProjectCreatedNotificationFactory $notificationFactory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        NotificationChecker $notificationChecker,
        ProjectStatusRepositoryInterface $projectStatusRepository,
        ProjectRepositoryInterface $projectRepository,
        SaverInterface $projectSaver
    ) {
        $this->notificationFactory = $notificationFactory;
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
            ProjectEvents::PROJECT_CALCULATED => 'projectCreated',
        ];
    }

    /**
     * Notify a contributor when a project is created.
     *
     * @param ProjectEvent $event
     */
    public function projectCreated(ProjectEvent $event)
    {
        $project = $event->getProject();
        $users = $this->userRepository->findContributorsToNotify($project);

        foreach ($users as $user) {
            if ($this->notificationChecker->isNotifiableForProjectCreation($project, $user)) {
                $notification = $this->notificationFactory->create($project, $user);
                $this->notifier->notify($notification, [$user]);
                $this->projectStatusRepository->setProjectStatus($project, $user, false);
                $this->setProjectCreated($project);
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
