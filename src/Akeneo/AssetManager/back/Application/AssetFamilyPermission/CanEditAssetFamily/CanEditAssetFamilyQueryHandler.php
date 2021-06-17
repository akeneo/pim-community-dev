<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\SecurityIdentifier;
use Akeneo\AssetManager\Domain\Query\UserGroup\FindUserGroupsForSecurityIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyPermissionRepositoryInterface;

/**
 * Query handler that determines whether editing the asset family for a principal id is authorized.
 *
 * The checks of the ACL (Access Control List) is done separately (usually in the adapters).
 * The ACL check may be done in this service in the future.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditAssetFamilyQueryHandler
{
    private AssetFamilyPermissionRepositoryInterface $assetFamilyPermissionRepository;

    private FindUserGroupsForSecurityIdentifierInterface $findUserGroupsForSecurityIdentifier;

    public function __construct(
        AssetFamilyPermissionRepositoryInterface $assetFamilyPermissionRepository,
        FindUserGroupsForSecurityIdentifierInterface $findUserGroupsForSecurityIdentifier
    ) {
        $this->assetFamilyPermissionRepository = $assetFamilyPermissionRepository;
        $this->findUserGroupsForSecurityIdentifier = $findUserGroupsForSecurityIdentifier;
    }

    public function __invoke(CanEditAssetFamilyQuery $query): bool
    {
        $assetFamilyPermission = $this->assetFamilyPermissionRepository->getByAssetFamilyIdentifier(
            AssetFamilyIdentifier::fromString($query->assetFamilyIdentifier)
        );
        $userGroupIdentifiers = $this->findUserGroupsForSecurityIdentifier->find(
            SecurityIdentifier::fromString($query->securityIdentifier)
        );

        return $assetFamilyPermission->isAllowedToEdit($userGroupIdentifiers);
    }
}
