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
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectNotifierInterface;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Factory\ProjectStatusFactoryInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatusInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notify users once a project is created or finished.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class NotificationSubscriber implements EventSubscriberInterface
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

    /** @var ProjectNotifierInterface */
    protected $projectCreatedNotifier;

    /** @var ProjectNotifierInterface */
    protected $projectFinishedNotifier;

    /**
     * @param UserRepositoryInterface                $userRepository
     * @param ProjectStatusFactoryInterface          $projectStatusFactory
     * @param ProjectStatusRepositoryInterface       $projectStatusRepository
     * @param SaverInterface                         $projectStatusSaver
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     * @param ProjectNotifierInterface               $projectCreatedNotifier
     * @param ProjectNotifierInterface               $projectFinishedNotifier
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ProjectStatusFactoryInterface $projectStatusFactory,
        ProjectStatusRepositoryInterface $projectStatusRepository,
        SaverInterface $projectStatusSaver,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        ProjectNotifierInterface $projectCreatedNotifier,
        ProjectNotifierInterface $projectFinishedNotifier
    ) {
        $this->userRepository = $userRepository;
        $this->projectStatusFactory = $projectStatusFactory;
        $this->projectStatusRepository = $projectStatusRepository;
        $this->projectStatusSaver = $projectStatusSaver;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->projectCreatedNotifier = $projectCreatedNotifier;
        $this->projectFinishedNotifier = $projectFinishedNotifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProjectEvents::PROJECT_CALCULATED => 'notify',
        ];
    }

    /**
     * @param ProjectEvent $event
     */
    public function notify(ProjectEvent $event)
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

            $this->checksAndNotifyConcernedUsed($projectStatus, $project, $user, $projectCompleteness);
        }
    }

    /**
     * Its check which users should be notified and notifies them.
     *
     * @param ProjectStatusInterface $projectStatus
     * @param ProjectInterface       $project
     * @param UserInterface          $user
     * @param ProjectCompleteness    $projectCompleteness
     */
    protected function checksAndNotifyConcernedUsed(
        ProjectStatusInterface $projectStatus,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        if ($this->projectCreatedNotifier->notifyUser($user, $project, $projectCompleteness)) {
            $projectStatus->setHasBeenNotified(true);

            $this->projectStatusSaver->save($projectStatus);
        }

        $this->projectFinishedNotifier->notifyUser($user, $project, $projectCompleteness);

        $projectStatus->setIsComplete($projectCompleteness->isComplete());

        $this->projectStatusSaver->save($projectStatus);
    }
}
