<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\ServiceApi;

use Akeneo\Category\ServiceApi\Category;
use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\Group;
use PHPUnit\Framework\Assert;

class EnterpriseCategoryQueryIntegration extends TestCase
{
    public function testItGetCategory(): void
    {
        $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $userGroup1 = $this->createUserGroup('group1');
        $userGroup2 = $this->createUserGroup('group2');
        $userGroup3 = $this->createUserGroup('group3');

        $this->get(UserGroupCategoryPermissionsSaver::class)->save($userGroup1->getName(), [
            'own' => ['all' => false, 'identifiers' => ['socks']],
            'edit' => ['all' => false, 'identifiers' => ['socks']],
            'view' => ['all' => false, 'identifiers' => ['socks']],
        ]);

        $this->get(UserGroupCategoryPermissionsSaver::class)->save($userGroup2->getName(), [
            'own' => ['all' => false, 'identifiers' => []],
            'edit' => ['all' => false, 'identifiers' => ['socks']],
            'view' => ['all' => false, 'identifiers' => ['socks']],
        ]);

        $this->get(UserGroupCategoryPermissionsSaver::class)->save($userGroup3->getName(), [
            'own' => ['all' => false, 'identifiers' => []],
            'edit' => ['all' => false, 'identifiers' => []],
            'view' => ['all' => false, 'identifiers' => ['socks']],
        ]);

        $category = $this->getHandler()->byCode('socks');

        Assert::assertInstanceOf(Category::class, $category);
        Assert::assertNotNull($category->getPermissions());
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

    private function getHandler(): CategoryQueryInterface
    {
        return $this->get(CategoryQueryInterface::class);
    }
}
