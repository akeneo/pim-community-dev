<?php

namespace Akeneo\Test\UserManagement\Integration\Infrastructure\ServiceApi\UserGroup;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\back\Application\Handler\ListUserGroupHandler;
use Akeneo\UserManagement\back\Infrastructure\ServiceApi\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\back\Infrastructure\ServiceApi\UserGroup\UserGroup;
use PHPUnit\Framework\Assert;

class ListUserGroupQueryIntegration extends TestCase
{
    public function testItListsTheUserGroups(): void
    {
        $userGroups = ($this->get(ListUserGroupHandler::class))(new ListUserGroupQuery());

        Assert::assertCount(4, $userGroups);
        Assert::containsOnlyInstancesOf(UserGroup::class);
    }

    public function testItFiltersTheUserGroupsOnLabel(): void
    {
        $userGroups = ($this->get(ListUserGroupHandler::class))(new ListUserGroupQuery('support'));

        Assert::assertCount(1, $userGroups);
        Assert::containsOnlyInstancesOf(UserGroup::class);

        Assert::assertMatchesRegularExpression('/it support/i', $userGroups[0]->getLabel());
    }

    public function testItListsTheUserGroupsWithPagination(): void
    {
        $userGroups = ($this->get(ListUserGroupHandler::class))(new ListUserGroupQuery(
            null,
            null,
            2
        ));

        Assert::assertCount(2, $userGroups);
        Assert::assertLessThan(2, $userGroups[0]->getId());
        Assert::assertEquals(2, $userGroups[1]->getId());

        $userGroups = ($this->get(ListUserGroupHandler::class))(new ListUserGroupQuery(
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
}
