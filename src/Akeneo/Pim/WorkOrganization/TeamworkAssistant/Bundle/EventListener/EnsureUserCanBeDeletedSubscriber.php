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

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserLinkedToProjectsQueryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserOwnerOfProjectsQueryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\UserBundle\Exception\UserCannotBeDeletedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class EnsureUserCanBeDeletedSubscriber implements EventSubscriberInterface
{
    private IsUserLinkedToProjectsQueryInterface $isUserLinkedToProjectQuery;
    private IsUserOwnerOfProjectsQueryInterface $isUserOwnerOfProjectsQuery;

    public function __construct(
        IsUserLinkedToProjectsQueryInterface $isUserLinkedToProjectQuery,
        IsUserOwnerOfProjectsQueryInterface $isUserOwnerOfProjectsQuery
    ) {
        $this->isUserLinkedToProjectQuery = $isUserLinkedToProjectQuery;
        $this->isUserOwnerOfProjectsQuery = $isUserOwnerOfProjectsQuery;
    }

    public static function getSubscribedEvents(): array
    {
        return [StorageEvents::PRE_REMOVE => 'ensureUserCanBeDeleted'];
    }

    public function ensureUserCanBeDeleted(GenericEvent $event): void
    {
        $user = $event->getSubject();
        if (!$user instanceof UserInterface) {
            return;
        }

        if (true === $this->isUserOwnerOfProjectsQuery->execute($user->getId())) {
            throw new UserCannotBeDeletedException('teamwork_assistant.user.deletion.user_is_project_owner');
        }

        if (true === $this->isUserLinkedToProjectQuery->execute($user->getId())) {
            throw new UserCannotBeDeletedException('teamwork_assistant.user.deletion.user_linked_to_project');
        }
    }
}
