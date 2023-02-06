<?php

declare(strict_types=1);

namespace Akeneo\Test\Catgory\Integration\Domain\ValueObject;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PermissionCollectionIntegration extends CategoryTestCase
{
    public function testItInitializeChangesetWhenCreatesPermissionCollectionFromArray(): void
    {
        $permissions = PermissionCollection::fromArray(
            [
                "view" => [
                    ['id' => 1, 'label' => "IT Support"],
                    ['id' => 3, 'label' => "Redactor"],
                    ['id' => 7, 'label' => "Manager"],
                ],
                "edit" => [
                    ['id' => 1, 'label' => "IT Support"],
                    ['id' => 3, 'label' => "Redactor"],
                    ['id' => 7, 'label' => "Manager"],
                ],
                "own" => [
                    ['id' => 1, 'label' => "IT Support"],
                    ['id' => 3, 'label' => "Redactor"],
                    ['id' => 7, 'label' => "Manager"],
                ],
            ]
        );
        $this->assertEmpty($permissions->getChangeset());
    }

    public function testItUpdatesChangesetWhenAddUserGroupsToExistingPermissionType(): void
    {
        $permissions = PermissionCollection::fromArray(
            [
                "view" => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'own' => [
                    ['id' => 1, 'label' => 'IT Support'],
                ],
            ]
        );
        $permissions->addUserGroupsToPermission('own', [
            ['id' => 7, 'label' => 'Manager'],
            ['id' => 3, 'label' => 'Redactor']
        ]);
        $changeset = $permissions->getChangeset();

        $expectedChangeset = [
            'own' => [
                'old' => [['id' => 1, 'label' => 'IT Support']],
                'new' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 7, 'label' => 'Manager'],
                    ['id' => 3, 'label' => 'Redactor']
                ],
            ]
        ];
        $this->assertEquals($expectedChangeset, $changeset);
    }

    public function testItUpdatesChangesetWhenAddUserGroupsToNonExistingPermissionType(): void
    {
        $permissions = PermissionCollection::fromArray(
            [
                'view' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ]
            ]
        );
        $permissions->addUserGroupsToPermission('own', [
            ['id' => 1, 'label' => 'IT Support'],
            ['id' => 3, 'label' => 'Redactor'],
            ['id' => 7, 'label' => 'Manager']
        ]);

        $changeset = $permissions->getChangeset();

        $expectedChangeset = [
            'own' => [
                'old' => [],
                'new' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager']
                ],
            ]
        ];
        $this->assertEquals($expectedChangeset, $changeset);
    }

    public function testItDoesNotUpdateChangesetWhenAddUserGroupsForAlreadyExistingUserGroup(): void
    {
        $permissions = PermissionCollection::fromArray(
            [
                'view' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'own' => [
                    ['id' => 1, 'label' => 'IT Support'],
                ],
            ]
        );
        $existingUserGroup = ['id' => 1, 'label' => 'IT Support'];
        $permissions->addUserGroupsToPermission('own', [$existingUserGroup]);

        $changeset = $permissions->getChangeset();

        $expectedChangeset = [];
        $this->assertEquals($expectedChangeset, $changeset);
    }

    public function testItUpdatesChangesetWhenRemoveUserGroupsToExistingPermissionType(): void
    {
        $permissions = PermissionCollection::fromArray(
            [
                "view" => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'own' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 7, 'label' => 'Manager'],
                    ['id' => 3, 'label' => 'Redactor']
                ],
            ]
        );
        $permissions->removeUserGroupsFromPermission('own', [
            ['id' => 7, 'label' => 'Manager'],
            ['id' => 3, 'label' => 'Redactor']
        ]);
        $changeset = $permissions->getChangeset();

        $expectedChangeset = [
            'own' => [
                'old' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 7, 'label' => 'Manager'],
                    ['id' => 3, 'label' => 'Redactor']
                ],
                'new' => [
                    ['id' => 1, 'label' => 'IT Support']
                ],
            ]
        ];
        $this->assertEquals($expectedChangeset, $changeset);
    }

    public function testItDoesNotUpdateChangesetWhenRemoveUserGroupsForNonExistingPermission(): void
    {
        $permissions = PermissionCollection::fromArray(
            [
                'view' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ]
            ]
        );
        $nonExistingPermission = 'own';
        $userGroup = ['id' => 1, 'label' => 'IT Support'];
        $permissions->removeUserGroupsFromPermission($nonExistingPermission, [$userGroup]);

        $changeset = $permissions->getChangeset();

        $expectedChangeset = [];
        $this->assertEquals($expectedChangeset, $changeset);
    }

    public function testItDoesNotUpdateChangesetWhenRemoveUserGroupsForNonExistingUserGroup(): void
    {
        $permissions = PermissionCollection::fromArray(
            [
                'view' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'IT Support'],
                    ['id' => 3, 'label' => 'Redactor'],
                    ['id' => 7, 'label' => 'Manager'],
                ]
            ]
        );
        $userGroup = ['id' => 8, 'label' => 'All'];
        $permissions->removeUserGroupsFromPermission('edit', [$userGroup]);

        $changeset = $permissions->getChangeset();

        $expectedChangeset = [];
        $this->assertEquals($expectedChangeset, $changeset);
    }
}
