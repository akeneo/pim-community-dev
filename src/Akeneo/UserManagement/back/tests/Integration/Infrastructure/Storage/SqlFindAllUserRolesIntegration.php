<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Integration\Infrastructure\Storage;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Domain\Model\UserRole;
use Akeneo\UserManagement\Infrastructure\Storage\SqlFindAllUserRoles;
use PHPUnit\Framework\Assert;

class SqlFindAllUserRolesIntegration extends TestCase
{
    public function testItListsAllTheUserRoles(): void
    {
        $this->removeAllUserRoles();
        $this->insertUserRole('first role');
        $this->insertUserRole('second role');
        $this->insertUserRole('third role');

        $userRoles = $this->getQuery()();

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

    private function getQuery(): SqlFindAllUserRoles
    {
        return $this->get(SqlFindAllUserRoles::class);
    }
}
