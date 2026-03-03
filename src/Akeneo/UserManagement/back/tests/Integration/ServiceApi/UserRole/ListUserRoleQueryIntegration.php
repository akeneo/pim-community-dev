<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Integration\ServiceApi\UserRole;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\ServiceApi\UserRole\ListUserRoleInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\UserRole;
use PHPUnit\Framework\Assert;

class ListUserRoleQueryIntegration extends TestCase
{
    public function testItListsAllTheUserRoles(): void
    {
        $this->removeAllUserRoles();
        $this->insertUserRole('first role');
        $this->insertUserRole('second role');
        $this->insertUserRole('third role');

        $userRoles = $this->getHandler()->all();

        Assert::assertCount(3, $userRoles);
        Assert::containsOnlyInstancesOf(UserRole::class);
    }

    private function removeAllUserRoles(): void
    {
        $deleteSql = <<<SQL
            DELETE FROM `oro_access_role`
        SQL;

        $this->get('database_connection')->executeQuery($deleteSql);
    }

    private function insertUserRole(string $roleCode): void
    {
        $insertSql = <<<SQL
            INSERT INTO `oro_access_role` (`role`, `label`, `type`) VALUES (:roleCode, :roleLabel, :roleType)
        SQL;

        $this->get('database_connection')->executeQuery(
            $insertSql,
            [
                'roleCode' => $roleCode,
                'roleLabel' => $roleCode,
                'roleType' => 'default',
            ],
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getHandler(): ListUserRoleInterface
    {
        return $this->get(ListUserRoleInterface::class);
    }
}
