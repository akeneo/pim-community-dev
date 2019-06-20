<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions;

use Akeneo\ReferenceEntity\Domain\Model\Permission\ReferenceEntityPermission;
use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupPermission;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SetReferenceEntityPermissionsHandler
{
    /** @var ReferenceEntityPermissionRepositoryInterface */
    private $repository;

    public function __construct(ReferenceEntityPermissionRepositoryInterface $referenceEntityPermissionRepository)
    {
        $this->repository = $referenceEntityPermissionRepository;
    }

    public function __invoke(SetReferenceEntityPermissionsCommand $command)
    {
        $permissions = [];
        foreach ($command->permissionsByUserGroup as $permissionByUserGroup) {
            $permissions[] = UserGroupPermission::create(
                UserGroupIdentifier::fromInteger($permissionByUserGroup->userGroupIdentifier),
                RightLevel::fromString($permissionByUserGroup->rightLevel)
            );
        }

        $referenceEntityPermissions = ReferenceEntityPermission::create(
            ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier),
            $permissions
        );

        $this->repository->save($referenceEntityPermissions);
    }
}
