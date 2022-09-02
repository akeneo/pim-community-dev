<?php

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByUserGroupIdQueryInterface;
use Akeneo\UserManagement\ServiceApi\User\User;

class FindUsersByUserGroupIdQueryTest extends IntegrationTestCase
{
    private FindUsersByUserGroupIdQueryInterface $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get('akeneo.job_automation.query.find_users_by_usergroup_id');
    }

    public function test_it_finds_users_by_group_id(): void
    {
        $expectedUsers = [
            new UserToNotify('peter', 'peter@example.com'),
        ];

        $this->assertEqualsCanonicalizing($expectedUsers, $this->query->execute([5]));

        $expectedUsers = [
            new UserToNotify('julia', 'julia@example.com'),
            new UserToNotify('peter', 'peter@example.com'),
            new UserToNotify('michel', 'michel@example.com'),
        ];

        $this->assertEqualsCanonicalizing($expectedUsers, $this->query->execute([1, 2, 5]));
    }
}
