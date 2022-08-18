<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Integration\ServiceApi\UserGroup;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupInterface;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroup;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroupQuery;
use PHPUnit\Framework\Assert;

class ListUserGroupQueryIntegration extends TestCase
{
    public function testItListsTheUserGroups(): void
    {
        $userGroups = $this->getHandler()->fromQuery(new UserGroupQuery());

        Assert::assertCount(4, $userGroups);
        Assert::containsOnlyInstancesOf(UserGroup::class);
    }

    public function testItFiltersTheUserGroupsOnLabel(): void
    {
        $userGroups = $this->getHandler()->fromQuery(new UserGroupQuery('support'));

        Assert::assertCount(1, $userGroups);
        Assert::containsOnlyInstancesOf(UserGroup::class);

        Assert::assertMatchesRegularExpression('/it support/i', $userGroups[0]->getLabel());
    }

    public function testItListsTheUserGroupsWithPagination(): void
    {
        $userGroups = $this->getHandler()->fromQuery(new UserGroupQuery(
            null,
            null,
            2
        ));

        Assert::assertCount(2, $userGroups);
        Assert::assertLessThan(2, $userGroups[0]->getId());
        Assert::assertEquals(2, $userGroups[1]->getId());

        $userGroups = $this->getHandler()->fromQuery(new UserGroupQuery(
            null,
            2,
            2
        ));

        Assert::assertCount(2, $userGroups);
        Assert::assertGreaterThan(2, $userGroups[0]->getId());
        Assert::assertGreaterThan(2, $userGroups[1]->getId());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getHandler(): ListUserGroupInterface
    {
        return $this->get(ListUserGroupInterface::class);
    }
}
