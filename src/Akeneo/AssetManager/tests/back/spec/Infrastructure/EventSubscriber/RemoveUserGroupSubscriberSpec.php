<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\EventSubscriber;

use Akeneo\AssetManager\Infrastructure\EventSubscriber\ResourceDeletionDeniedException;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamilyPermission\SqlFindAssetFamilyWhereUserGroupIsLastToHaveEditRight;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use PhpSpec\ObjectBehavior;

class RemoveUserGroupSubscriberSpec extends ObjectBehavior
{
    function let(SqlFindAssetFamilyWhereUserGroupIsLastToHaveEditRight $findAssetFamilyWhereUserGroupIsLastToHaveEditRight)
    {
        $this->beConstructedWith($findAssetFamilyWhereUserGroupIsLastToHaveEditRight);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::PRE_REMOVE => 'checkUserGroupPermissionsOnAssetFamily',
            ]
        );
    }

    function it_checks_the_user_group_is_not_the_last_able_to_edit_an_asset_family(
        SqlFindAssetFamilyWhereUserGroupIsLastToHaveEditRight $findAssetFamilyWhereUserGroupIsLastToHaveEditRight,
        GroupInterface $userGroup,
        RemoveEvent $event
    ) {
        $event->getSubject()->willReturn($userGroup);
        $userGroup->getId()->willReturn(10);

        $findAssetFamilyWhereUserGroupIsLastToHaveEditRight->find(10)->willReturn(['designer']);

        $this->shouldThrow(ResourceDeletionDeniedException::class)
            ->during('checkUserGroupPermissionsOnAssetFamily', [$event]);
    }

    function it_does_nothing_if_user_group_can_be_deleted(
        SqlFindAssetFamilyWhereUserGroupIsLastToHaveEditRight $findAssetFamilyWhereUserGroupIsLastToHaveEditRight,
        GroupInterface $userGroup,
        RemoveEvent $event
    ) {
        $event->getSubject()->willReturn($userGroup);
        $userGroup->getId()->willReturn(10);

        $findAssetFamilyWhereUserGroupIsLastToHaveEditRight->find(10)->willReturn([]);

        $this->checkUserGroupPermissionsOnAssetFamily($event);
    }
}
