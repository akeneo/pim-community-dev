<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql\Category;

use Akeneo\Pim\Permission\Bundle\Category\GetCategoryProductPermissions;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\Group;
use PHPUnit\Framework\Assert;

class GetCategoryProductPermissionsSqlIntegration extends TestCase
{
    public function testItGetPermissions(): void
    {
        $category = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);
        $userGroup1 = $this->createUserGroup('group1');
        $this->get(UserGroupCategoryPermissionsSaver::class)->save($userGroup1->getName(), [
            'own' => ['all' => false, 'identifiers' => ['socks']],
            'edit' => ['all' => false, 'identifiers' => ['socks']],
            'view' => ['all' => false, 'identifiers' => ['socks']],
        ]);
        $userGroupAll = $this->get('pim_user.repository.group')->findOneByIdentifier("All");

        $expectedPermissions = [
            "own" => [
                [
                    "id" => $userGroupAll->getId(),
                    "label" => $userGroupAll->getName()
                ],
                [
                    "id" => $userGroup1->getId(),
                    "label" => $userGroup1->getName()
                ]
            ],
            "edit" => [
                [
                    "id" => $userGroupAll->getId(),
                    "label" => $userGroupAll->getName()
                ],
                [
                    "id" => $userGroup1->getId(),
                    "label" => $userGroup1->getName()
                ]
            ],
            "view" => [
                [
                    "id" => $userGroupAll->getId(),
                    "label" => $userGroupAll->getName()
                ],
                [
                    "id" => $userGroup1->getId(),
                    "label" => $userGroup1->getName()
                ]
            ],
        ];

        $permissions = ($this->get(GetCategoryProductPermissions::class))($category->getId());
        Assert::assertEquals($expectedPermissions, $permissions);
    }

    public function testItGetNoPermissions(): void
    {
        $category = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);
        $fakeCategoryId = $category->getId() + 1;

        $expectedPermissions = [];

        $permissions = ($this->get(GetCategoryProductPermissions::class))($fakeCategoryId);

        Assert::assertNotNull($permissions);
        Assert::assertEquals($expectedPermissions, $permissions);
    }

    private function createUserGroup(string $name): Group
    {
        $userGroup = new Group();
        $userGroup->setName($name);

        $violations = $this->get('validator')->validate($userGroup);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_user.saver.group')->save($userGroup);

        return $userGroup;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
