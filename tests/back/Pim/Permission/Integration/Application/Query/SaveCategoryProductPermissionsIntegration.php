<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Application\Query;

use Akeneo\Pim\Permission\Bundle\Application\Query\SaveCategoryProductPermissionsInterface;
use Akeneo\Pim\Permission\Bundle\Category\GetCategoryProductPermissions;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\Group;
use PHPUnit\Framework\Assert;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
final class SaveCategoryProductPermissionsIntegration extends TestCase
{
    public function testItSavesPermissions(): void
    {
        $category = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $userGroup1 = $this->createUserGroup('group1');
        $userGroup2 = $this->createUserGroup('group2');

        $query = <<<SQL
            INSERT INTO pimee_security_product_category_access (user_group_id, category_id, view_items, edit_items, own_items)
            VALUES 
                (:user_group_id, :category_id, 1, 1, 1)
        SQL;

        $this->get('database_connection')->executeQuery($query, [
            'user_group_id' => $userGroup1->getId(),
            'category_id' => $category->getId(),
        ]);

        ($this->get(SaveCategoryProductPermissionsInterface::class))($category->getId(), [
            'view' => [$userGroup2->getId()],
            'edit' => [$userGroup2->getId()],
            'own' => [$userGroup2->getId()],
        ]);

        $permissions = ($this->get(GetCategoryProductPermissions::class))($category->getId());

        $this->assertContains($userGroup2->getId(), array_map(fn ($permission) => $permission['id'], $permissions['view']));
        $this->assertContains($userGroup2->getId(), array_map(fn ($permission) => $permission['id'], $permissions['edit']));
        $this->assertContains($userGroup2->getId(), array_map(fn ($permission) => $permission['id'], $permissions['own']));
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
