<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Pim\Permission\Component\Exception\ResourceDeletionDeniedException;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntityPermission\SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use PhpSpec\ObjectBehavior;

class RemoveUserGroupSubscriberSpec extends ObjectBehavior
{
    function let(SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight $findReferenceEntityWhereUserGroupIsLastToHaveEditRight)
    {
        $this->beConstructedWith($findReferenceEntityWhereUserGroupIsLastToHaveEditRight);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::PRE_REMOVE => 'checkUserGroupPermissionsOnReferenceEntity',
            ]
        );
    }

    function it_checks_the_user_group_is_not_the_last_able_to_edit_a_reference_entity(
        SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight $findReferenceEntityWhereUserGroupIsLastToHaveEditRight,
        GroupInterface $userGroup,
        RemoveEvent $event
    ) {
        $event->getSubject()->willReturn($userGroup);
        $userGroup->getId()->willReturn(10);

        $findReferenceEntityWhereUserGroupIsLastToHaveEditRight->__invoke(10)->willReturn(['designer']);

        $this->shouldThrow(ResourceDeletionDeniedException::class)
            ->during('checkUserGroupPermissionsOnReferenceEntity', [$event]);
    }

    function it_does_nothing_if_user_group_can_be_deleted(
        SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight $findReferenceEntityWhereUserGroupIsLastToHaveEditRight,
        GroupInterface $userGroup,
        RemoveEvent $event
    ) {
        $event->getSubject()->willReturn($userGroup);
        $userGroup->getId()->willReturn(10);

        $findReferenceEntityWhereUserGroupIsLastToHaveEditRight->__invoke(10)->willReturn([]);

        $this->checkUserGroupPermissionsOnReferenceEntity($event);
    }
}
