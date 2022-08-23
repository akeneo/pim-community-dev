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
        $userRoles = $this->getQuery()();

        Assert::assertCount(5, $userRoles);
        Assert::containsOnlyInstancesOf(UserRole::class);
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
