<?php

namespace Akeneo\Test\UserManagement\Integration\Infrastructure\Storage;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\back\Domain\Model\Group;
use Akeneo\UserManagement\back\Infrastructure\Storage\SqlFindUserGroups;
use PHPUnit\Framework\Assert;

class SqlFindUserGroupsIntegration extends TestCase
{
    public function testItListsTheUserGroups(): void
    {
        $userGroups = ($this->get(SqlFindUserGroups::class))();

        Assert::assertCount(4, $userGroups);
        Assert::containsOnlyInstancesOf(Group::class);
    }

    public function testItFiltersTheUserGroupsOnLabel(): void
    {
        $userGroups = ($this->get(SqlFindUserGroups::class))('support');

        Assert::assertCount(1, $userGroups);
        Assert::containsOnlyInstancesOf(Group::class);

        Assert::assertMatchesRegularExpression('/it support/i', $userGroups[0]->getName());
    }

    public function testItListsTheUserGroupsWithPagination(): void
    {
        $userGroups = ($this->get(SqlFindUserGroups::class))(
            null,
            null,
            2
        );

        Assert::assertCount(2, $userGroups);
        Assert::assertLessThan(2, $userGroups[0]->getId());
        Assert::assertEquals(2, $userGroups[1]->getId());

        $userGroups = ($this->get(SqlFindUserGroups::class))(
            null,
            2,
            2
        );

        Assert::assertCount(2, $userGroups);
        Assert::assertGreaterThan(2, $userGroups[0]->getId());
        Assert::assertGreaterThan(2, $userGroups[1]->getId());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

}
