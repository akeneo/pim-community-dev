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
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
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
     * @param ProjectCreatedNotificationFactory      $factory
     * @param NotifierInterface                      $notifier
     * @param UserRepositoryInterface                $userRepository
     * @param PresenterInterface                     $datePresenter
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     */
    public function __construct(
        ProjectCreatedNotificationFactory $factory,
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
            $completeness = $completeness['done']/array_sum($completeness) * 100;

            if (99 < (int) $completeness) {
                continue;
            }

            $userLocale = $user->getUiLocale();
            $formattedDate = $this->datePresenter->present(
                $project->getDueDate(),
                ['locale' => $userLocale->getCode()]
            );

            $parameters['due_date'] = $formattedDate;
            $parameters['project_label'] = $project->getLabel();
            $parameters['project_code'] = $project->getCode();

            $notification = $this->factory->create($parameters);
            $this->notifier->notify($notification, [$user]);
        }
    }
}
