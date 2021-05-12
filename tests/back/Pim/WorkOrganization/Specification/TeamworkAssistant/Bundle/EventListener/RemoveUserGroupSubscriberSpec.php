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

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Exception\CannotRemoveUserGroupLinkedToProjectException;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserGroupAttachedToProject;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RemoveUserGroupSubscriberSpec extends ObjectBehavior
{
    function let(
        IsUserGroupAttachedToProject $isUserGroupAttachedToProject,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith($isUserGroupAttachedToProject, $translator);
    }

    function it_does_nothing_when_subject_is_not_a_group(IsUserGroupAttachedToProject $isUserGroupAttachedToProject)
    {
        $event = new GenericEvent(new \stdClass());

        $isUserGroupAttachedToProject->forUserGroupId(Argument::any())->shouldNotBeCalled();

        $this->checkUserGroupIsNotAttachedToAProject($event);
    }

    function it_does_nothing_when_group_is_not_attached_to_a_project(
        IsUserGroupAttachedToProject $isUserGroupAttachedToProject,
        GenericEvent $event,
        GroupInterface $group
    ) {
        $group->getId()->willReturn(1);
        $event->getSubject()->willReturn($group);

        $isUserGroupAttachedToProject->forUserGroupId(1)->willReturn(false);

        $this->checkUserGroupIsNotAttachedToAProject($event);
    }

    function it_throws_an_exception_when_group_is_attached_to_a_project(
        IsUserGroupAttachedToProject $isUserGroupAttachedToProject,
        TranslatorInterface $translator,
        GenericEvent $event,
        GroupInterface $group
    ) {
        $group->getId()->willReturn(1);
        $event->getSubject()->willReturn($group);

        $isUserGroupAttachedToProject->forUserGroupId(1)->willReturn(true);
        $translator->trans('teamwork_assistant.user_group.deletion.group_linked_to_project')->willReturn(
            'translation'
        );

        $this->shouldThrow(CannotRemoveUserGroupLinkedToProjectException::class)
            ->during('checkUserGroupIsNotAttachedToAProject', [$event]);
    }
}
