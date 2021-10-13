<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\FixturesLoader;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class UserGroupPermissionsFixturesLoader
{
    private SaverInterface $userGroupSaver;

    public function __construct(
        SaverInterface $userGroupSaver
    ) {
        $this->userGroupSaver = $userGroupSaver;
    }

    /**
     * @param array{
     *     category_own: bool,
     *     category_edit: bool,
     *     category_view: bool,
     *     attribute_group_edit: bool,
     *     attribute_group_view: bool,
     *     locale_edit: bool,
     *     locale_view: bool
     * } $defaultPermissions
     */
    public function givenTheUserGroupDefaultPermissions(GroupInterface $userGroup, array $defaultPermissions): void
    {
        foreach ($defaultPermissions as $permissionName => $granted) {
            $userGroup->setDefaultPermission($permissionName, $granted);
        }

        $this->userGroupSaver->save($userGroup);
    }
}
