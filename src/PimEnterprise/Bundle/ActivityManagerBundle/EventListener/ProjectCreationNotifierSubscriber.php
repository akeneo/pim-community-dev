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

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
use Symfony\Component\Security\Core\User\UserInterface;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\NotificationHistoryRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
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

    /** @var PresenterInterface */
    protected $datePresenter;

    /** @var ProjectCompletenessRepositoryInterface */
    protected $projectCompletenessRepository;

    /** @var SaverInterface */
    protected $notificationHistorySaver;

    /** @var SimpleFactoryInterface */
    protected $notificationHistoryFactory;

    /** @var NotificationHistoryRepositoryInterface */
    protected $notificationHistoryRepository;

    /**
     * @param ProjectCreatedNotificationFactory      $notificationFactory
     * @param NotifierInterface                      $notifier
     * @param UserRepositoryInterface                $userRepository
     * @param PresenterInterface                     $datePresenter
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     * @param NotificationHistoryRepositoryInterface $notificationHistoryRepository
     * @param SaverInterface                         $notificationHistorySaver
     * @param SimpleFactoryInterface                 $notificationHistoryFactory
     */
    public function __construct(
        ProjectCreatedNotificationFactory $notificationFactory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        PresenterInterface $datePresenter,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        NotificationHistoryRepositoryInterface $notificationHistoryRepository,
        SaverInterface $notificationHistorySaver,
        SimpleFactoryInterface $notificationHistoryFactory
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->notifier = $notifier;
        $this->userRepository = $userRepository;
        $this->datePresenter = $datePresenter;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->notificationHistorySaver = $notificationHistorySaver;
        $this->notificationHistoryFactory = $notificationHistoryFactory;
        $this->notificationHistoryRepository = $notificationHistoryRepository;
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
        $users = $this->userRepository->findContributorsToNotify($project);

        foreach ($users as $user) {
            $completeness = $this->projectCompletenessRepository->getProjectCompleteness($project, $user);

            if (!$completeness->isComplete()) {
                if (!$this->notificationHistoryRepository->hasBeenNotifiedForProjectCreation($project, $user)) {
                    $userLocale = $user->getUiLocale();
                    $formattedDate = $this->datePresenter->present(
                        $project->getDueDate(),
                        ['locale' => $userLocale->getCode()]
                    );

                    $parameters['due_date'] = $formattedDate;
                    $parameters['project_label'] = $project->getLabel();
                    $parameters['project_code'] = $project->getCode();

                    $this->addToNotificationHistory($project, $user);
                    $notification = $this->notificationFactory->create($parameters);
                    $this->notifier->notify($notification, [$user]);
                }
            }
        }
    }

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     */
    protected function addToNotificationHistory($project, $user)
    {
        $notificationHistory = $this->notificationHistoryRepository->findNotificationHistory($project, $user);

        if (null === $notificationHistory) {
            $notificationHistory = $this->notificationHistoryFactory->create();
            $notificationHistory->setUser($user);
            $notificationHistory->setProject($project);
        }

        $notificationHistory->setNotificationProjectCreation(true);
        $this->notificationHistorySaver->save($notificationHistory);
    }
}
