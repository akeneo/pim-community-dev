<?php

namespace spec\Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily;

use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Model\Permission\AssetFamilyPermission;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Model\SecurityIdentifier;
use Akeneo\AssetManager\Domain\Query\UserGroup\FindUserGroupsForSecurityIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyPermissionRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CanEditAssetFamilyQueryHandlerSpec extends ObjectBehavior
{
    function let(
        AssetFamilyPermissionRepositoryInterface $assetFamilyPermissionRepository,
        FindUserGroupsForSecurityIdentifierInterface $findUserGroupsForSecurityIdentifier
    ) {
        $this->beConstructedWith($assetFamilyPermissionRepository, $findUserGroupsForSecurityIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CanEditAssetFamilyQueryHandler::class);
    }

    function it_asks_the_asset_family_permission_if_the_user_is_allowed(
        AssetFamilyPermissionRepositoryInterface $assetFamilyPermissionRepository,
        FindUserGroupsForSecurityIdentifierInterface $findUserGroupsForSecurityIdentifier,
        AssetFamilyPermission $assetFamilyPermission,
        UserGroupIdentifier $userGroupIdentifier1,
        UserGroupIdentifier $userGroupIdentifier2
    ) {
        $query = new CanEditAssetFamilyQuery(
            'brand',
            'julia'
        );

        $assetFamilyPermissionRepository->getByAssetFamilyIdentifier(
            Argument::that(
                fn($assetFamilyIdentifier) => 'brand' === $assetFamilyIdentifier->normalize()
            )
        )->willReturn($assetFamilyPermission);

        $findUserGroupsForSecurityIdentifier->find(
            Argument::that(
                fn(SecurityIdentifier $securityIdentifier) => 'julia' === $securityIdentifier->stringValue()
            )
        )->willReturn([$userGroupIdentifier1, $userGroupIdentifier2]);

        $assetFamilyPermission->isAllowedToEdit([$userGroupIdentifier1, $userGroupIdentifier2])->willReturn(true);

        $this->__invoke($query)->shouldReturn(true);
    }
}
