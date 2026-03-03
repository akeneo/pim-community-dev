<?php

namespace Akeneo\Test\UserManagement\Integration\Infrastructure\Storage;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Domain\Model\Group;
use Akeneo\UserManagement\Infrastructure\Storage\SqlFindUserGroups;
use PHPUnit\Framework\Assert;

class SqlFindUserGroupsIntegration extends TestCase
{
    public function testItListsTheUserGroups(): void
    {
        $userGroups = $this->getQuery()();

        Assert::assertCount(4, $userGroups);
        Assert::containsOnlyInstancesOf(Group::class);
    }

    public function testItFiltersTheUserGroupsOnLabel(): void
    {
        $userGroups = $this->getQuery()('support');

        Assert::assertCount(1, $userGroups);
        Assert::containsOnlyInstancesOf(Group::class);

        Assert::assertMatchesRegularExpression('/it support/i', $userGroups[0]->getName());
    }

    public function testItListsTheUserGroupsWithPagination(): void
    {
        $userGroups = $this->getQuery()(
            null,
            null,
            2
        );

        Assert::assertCount(2, $userGroups);
        Assert::assertLessThan(2, $userGroups[0]->getId());
        Assert::assertEquals(2, $userGroups[1]->getId());

        $userGroups = $this->getQuery()(
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

    private function getQuery()
    {
        return $this->get(SqlFindUserGroups::class);
    }
}
