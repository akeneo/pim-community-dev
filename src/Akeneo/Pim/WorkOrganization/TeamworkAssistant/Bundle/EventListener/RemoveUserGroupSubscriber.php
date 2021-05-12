<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Exception\CannotRemoveUserGroupLinkedToProjectException;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserGroupAttachedToProject;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class RemoveUserGroupSubscriber implements EventSubscriberInterface
{
    private IsUserGroupAttachedToProject $isUserGroupAttachedToProject;
    protected TranslatorInterface $translator;

    public function __construct(
        IsUserGroupAttachedToProject $isUserGroupAttachedToProject,
        TranslatorInterface $translator
    ) {
        $this->isUserGroupAttachedToProject = $isUserGroupAttachedToProject;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'checkUserGroupIsNotAttachedToAProject',
        ];
    }

    /**
     * @throws CannotRemoveUserGroupLinkedToProjectException
     */
    public function checkUserGroupIsNotAttachedToAProject(GenericEvent $event): void
    {
        $userGroup = $event->getSubject();
        if (!$userGroup instanceof GroupInterface) {
            return;
        }

        if ($this->isUserGroupAttachedToProject->forUserGroupId($userGroup->getId())) {
            throw new CannotRemoveUserGroupLinkedToProjectException(
                $this->translator->trans('teamwork_assistant.user_group.deletion.group_linked_to_project')
            );
        }
    }
}
