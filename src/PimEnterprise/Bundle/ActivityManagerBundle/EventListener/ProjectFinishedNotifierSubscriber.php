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

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectFinishedNotificationFactory;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
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

    /** @var PresenterInterface */
    protected $datePresenter;

    /** @var ProjectCompletenessRepositoryInterface */
    protected $projectCompletenessRepository;

    /**
     * @param ProjectFinishedNotificationFactory     $factory
     * @param NotifierInterface                      $notifier
     * @param UserRepositoryInterface                $userRepository
     * @param PresenterInterface                     $datePresenter
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     */
    public function __construct(
        ProjectFinishedNotificationFactory $factory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        PresenterInterface $datePresenter,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository
    ) {
        $this->factory = $factory;
        $this->notifier = $notifier;
        $this->userRepository = $userRepository;
        $this->datePresenter = $datePresenter;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
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

        $projectCompleteness = $this->projectCompletenessRepository
            ->getProjectCompleteness($project);

        if ($projectCompleteness->isComplete()) {
            $this->notifyOwner($project);
            $this->notifyContributors($project);

            return;
        }

        $this->notifyContributors($project);
    }

    /**
     * @param ProjectInterface $project
     */
    protected function notifyOwner($project)
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

        $notification = $this->factory->create('activity_manager.notification.project_finished.owner', $parameters);
        $this->notifier->notify($notification, [$project->getOwner()]);
    }

    /**
     * @param ProjectInterface $project
     */
    protected function notifyContributors(ProjectInterface $project)
    {
        $contributors = $this->userRepository->findContributorsToNotify($project);
        $parameters = ['%project_label%' => '"' . $project->getLabel() . '"', 'project_code' => $project->getCode()];
        foreach ($contributors as $contributor) {
            $contributorCompleteness = $this->projectCompletenessRepository
                ->getProjectCompleteness($project, $contributor);

            if ($contributorCompleteness->isComplete()) {
                $notification = $this->factory->create(
                    'activity_manager.notification.project_finished.contributor',
                    $parameters
                );
                $this->notifier->notify($notification, [$contributor]);
            }
        }
    }
}
