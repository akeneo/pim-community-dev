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
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectFinishedNotificationFactory;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Factory\ProjectStatusFactoryInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class HistoryNotificationSystemSubscriber implements EventSubscriberInterface
{
    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var ProjectStatusFactoryInterface */
    protected $projectStatusFactory;

    /** @var ProjectStatusRepositoryInterface */
    protected $projectStatusRepository;

    /** @var SaverInterface */
    protected $projectStatusSaver;

    /** @var ProjectCompletenessRepositoryInterface */
    protected $projectCompletenessRepository;

    /** @var ProjectCreatedNotificationFactory */
    protected $projectCreatedNotificationFactory;

    /** @var ProjectFinishedNotificationFactory */
    protected $projectFinishedNotificationFactory;

    /** @var NotifierInterface */
    protected $notifier;

    /**
     * @param UserRepositoryInterface                $userRepository
     * @param ProjectStatusFactoryInterface          $projectStatusFactory
     * @param ProjectStatusRepositoryInterface       $projectStatusRepository
     * @param SaverInterface                         $projectStatusSaver
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     * @param ProjectCreatedNotificationFactory      $projectCreatedNotificationFactory
     * @param ProjectFinishedNotificationFactory     $projectFinishedNotificationFactory
     * @param NotifierInterface                      $notifier
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ProjectStatusFactoryInterface $projectStatusFactory,
        ProjectStatusRepositoryInterface $projectStatusRepository,
        SaverInterface $projectStatusSaver,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        ProjectCreatedNotificationFactory $projectCreatedNotificationFactory,
        ProjectFinishedNotificationFactory $projectFinishedNotificationFactory,
        NotifierInterface $notifier
    ) {
        $this->userRepository = $userRepository;
        $this->projectStatusFactory = $projectStatusFactory;
        $this->projectStatusRepository = $projectStatusRepository;
        $this->projectStatusSaver = $projectStatusSaver;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->projectCreatedNotificationFactory = $projectCreatedNotificationFactory;
        $this->projectFinishedNotificationFactory = $projectFinishedNotificationFactory;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProjectEvents::PROJECT_CALCULATED => 'test',
        ];
    }

    /**
     * @param ProjectEvent $event
     */
    public function test(ProjectEvent $event)
    {
        $project = $event->getProject();
        $users = $this->userRepository->findUsersToNotify($project);

        foreach ($users as $user) {
            $projectCompleteness = $this->projectCompletenessRepository->getProjectCompleteness($project, $user);
            $projectStatus = $this->projectStatusRepository->findProjectStatus($project, $user);

            if (null === $projectStatus) {
                $projectStatus = $this->projectStatusFactory->create($project, $user);
                $projectStatus->setHasBeenNotified(false);
                $projectStatus->setIsComplete($projectCompleteness->isComplete());
                $this->projectStatusSaver->save($projectStatus);
            }

            if ($user !== $project->getOwner()) {
                if (!$projectStatus->hasBeenNotified() && !$projectCompleteness->isComplete()) {
                    $this->notifyContributorForProjectCreated($project, $user);
                    $projectStatus->setHasBeenNotified(true);
                    $projectStatus->setIsComplete(false);
                    $this->projectStatusSaver->save($projectStatus);
                }

                if ($projectCompleteness->isComplete() && !$projectStatus->isComplete()) {
                    $this->notifyContributorForProjectFinished($project, $user);
                    $projectStatus->setIsComplete(true);
                    $this->projectStatusSaver->save($projectStatus);
                }

                if (!$projectCompleteness->isComplete()) {
                    $projectStatus->setIsComplete(false);
                    $this->projectStatusSaver->save($projectStatus);
                }
            } else {
                if ($projectCompleteness->isComplete() && !$projectStatus->isComplete()) {
                    $this->notifyOwnerForProjectFinished($project, $user);
                    $projectStatus->setIsComplete(true);
                    $this->projectStatusSaver->save($projectStatus);
                }
            }
        }
    }

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     */
    protected function notifyContributorForProjectCreated(ProjectInterface $project, UserInterface $user)
    {
        $notification = $this->projectCreatedNotificationFactory->create($project, $user);
        $this->notifier->notify($notification, [$user]);
    }

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $owner
     */
    protected function notifyOwnerForProjectFinished(ProjectInterface $project, UserInterface $owner)
    {
        $notification = $this->projectFinishedNotificationFactory
            ->create($project, 'activity_manager.notification.project_finished.owner');
        $this->notifier->notify($notification, [$owner]);
    }

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     */
    protected function notifyContributorForProjectFinished(ProjectInterface $project, UserInterface $user)
    {
        $notification = $this->projectFinishedNotificationFactory->create(
            $project,
            'activity_manager.notification.project_finished.contributor'
        );
        $this->notifier->notify($notification, [$user]);
    }
}
