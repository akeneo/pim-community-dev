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
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Oro\Bundle\UserBundle\Exception\UserCannotBeDeletedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class EnsureUserCanBeDeletedSubscriber implements EventSubscriberInterface
{
    /** @var IsUserLinkedToProjectsQueryInterface */
    private $isUserLinkedToProjectQuery;

    public function __construct(IsUserLinkedToProjectsQueryInterface $isUserLinkedToProjectQuery)
    {
        $this->isUserLinkedToProjectQuery = $isUserLinkedToProjectQuery;
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

        if (true === $this->isUserLinkedToProjectQuery->execute($user->getId())) {
            throw new UserCannotBeDeletedException('teamwork_assistant.user.deletion.user_linked_to_project');
        }
    }
}
