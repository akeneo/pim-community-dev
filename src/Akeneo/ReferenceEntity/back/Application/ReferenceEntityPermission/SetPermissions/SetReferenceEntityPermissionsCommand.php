<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions;

/**
 * This command represents all the user group permissions set for one reference entity
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SetReferenceEntityPermissionsCommand
{
    /** @var string */
    public $referenceEntityIdentifier;

    /** @var SetUserGroupPermissionCommand[] */
    public $permissionsByUserGroup = [];

    public function __construct(string $referenceEntityIdentifier, array $permissionsByUserGroup)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->permissionsByUserGroup = $permissionsByUserGroup;
    }
}
