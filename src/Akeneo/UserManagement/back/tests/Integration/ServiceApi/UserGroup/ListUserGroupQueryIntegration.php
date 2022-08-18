<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Integration\ServiceApi\UserGroup;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupHandlerInterface;
use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroup;
use PHPUnit\Framework\Assert;

class ListUserGroupQueryIntegration extends TestCase
{
    public function testItListsTheUserGroups(): void
    {
        $userGroups = ($this->get(ListUserGroupHandlerInterface::class))(new ListUserGroupQuery());

        Assert::assertCount(4, $userGroups);
        Assert::containsOnlyInstancesOf(UserGroup::class);
    }

    public function testItFiltersTheUserGroupsOnLabel(): void
    {
        $userGroups = ($this->get(ListUserGroupHandlerInterface::class))(new ListUserGroupQuery('support'));

        Assert::assertCount(1, $userGroups);
        Assert::containsOnlyInstancesOf(UserGroup::class);

        Assert::assertMatchesRegularExpression('/it support/i', $userGroups[0]->getLabel());
    }

    public function testItListsTheUserGroupsWithPagination(): void
    {
        $userGroups = ($this->get(ListUserGroupHandlerInterface::class))(new ListUserGroupQuery(
            null,
            null,
            2
        ));

        Assert::assertCount(2, $userGroups);
        Assert::assertLessThan(2, $userGroups[0]->getId());
        Assert::assertEquals(2, $userGroups[1]->getId());

        $userGroups = ($this->get(ListUserGroupHandlerInterface::class))(new ListUserGroupQuery(
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
