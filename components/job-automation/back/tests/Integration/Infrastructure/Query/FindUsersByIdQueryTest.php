<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByIdQueryInterface;

class FindUsersByIdQueryTest extends IntegrationTestCase
{
    private FindUsersByIdQueryInterface $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get('akeneo.job_automation.query.find_users_by_id');
    }

    public function test_it_finds_users_by_id(): void
    {
        $expectedUsers = [
            new UserToNotify('julia', 'julia@example.com'),
            new UserToNotify('adrien', 'adrien@example.com'),
        ];

        $this->assertEqualsCanonicalizing($expectedUsers, $this->query->execute([1, 4]));
    }
}
