<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Integration\ServiceApi\UserRole;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\ServiceApi\UserRole\ListUserRoleInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\UserRole;
use Akeneo\UserManagement\ServiceApi\UserRole\UserRoleQuery;
use PHPUnit\Framework\Assert;

class ListUserRoleQueryIntegration extends TestCase
{
    public function testItListsAllTheUserRoles(): void
    {
        $userRoles = $this->getHandler()->all();

        Assert::assertCount(5, $userRoles);
        Assert::containsOnlyInstancesOf(UserRole::class);
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
