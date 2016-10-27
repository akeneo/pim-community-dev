<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\EventSubscriber;

use Akeneo\ActivityManager\Component\Event\ProjectEvent;
use Akeneo\ActivityManager\Component\Event\ProjectEvents;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\ActivityManager\Component\Repository\UserRepositoryInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Job execution notifier
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class JobExecutionNotifier implements EventSubscriberInterface
{
    /** @var */
    private $factory;

    /** @var NotifierInterface */
    private $notifier;

    /** @var ProjectRepositoryInterface */
    private $projectRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @param  $factory
     * @param NotifierInterface           $notifier
     * @param ProjectRepositoryInterface  $projectRepository
     * @param UserRepositoryInterface     $userRepository
     */
    public function __construct(
        $factory,
        NotifierInterface $notifier,
        ProjectRepositoryInterface $projectRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->factory = $factory;
        $this->notifier = $notifier;
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
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
     * Notify a user when a project is created
     *
     * @param ProjectEvent $event
     */
    public function projectCreated(ProjectEvent $event)
    {
        $project = $event->getProject();

        if (!$project instanceof ProjectInterface) {
            return;
        }

        $userGroups = $project->getUserGroups();
        $owner = $project->getOwner();

        $userGroupIds = [];
        foreach ($userGroups as $userGroup) {
            $userGroupIds[] = $userGroup->getId();
        }

        $users = $this->userRepository->findByGroupIdsOwnerExcluded($owner->getId(), $userGroupIds);

        $notification = $this->factory->create();
        $this->notifier->notify($notification, $users);
    }
}
