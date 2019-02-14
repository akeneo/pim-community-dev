<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\DeleteProjectStatusIfUserIsNotLinkedToProject;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DeleteProjectStatusIfUserIsNotLinkedToProjectSubscriber implements EventSubscriberInterface
{
    /** @var DeleteProjectStatusIfUserIsNotLinkedToProject */
    private $deleteProjectStatusIfUserIsNotLinkedToProject;

    public function __construct(DeleteProjectStatusIfUserIsNotLinkedToProject $deleteProjectStatusIfUserIsNotLinkedToProject)
    {
        $this->deleteProjectStatusIfUserIsNotLinkedToProject = $deleteProjectStatusIfUserIsNotLinkedToProject;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [StorageEvents::PRE_REMOVE => 'removeUserFromProject'];
    }

    public function removeUserFromProject(GenericEvent $event): void
    {
        $user = $event->getSubject();
        if (!$user instanceof UserInterface) {
            return;
        }

        ($this->deleteProjectStatusIfUserIsNotLinkedToProject)($user->getId());
    }
}
