<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class FindJobUsersTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturesLoader->loadProductImportExportFixtures();


        $jobInstances = [
            'find_job_user_test' => $this->fixturesLoader->createJobInstance([
                'code' => 'find_job_user_test',
                'job_name' => 'find_job_user_test',
                'label' => 'find_job_user_test',
                'type' => 'import',
            ]),
        ];
        $jobExecutions = [
            'a_job_execution' => $this->fixturesLoader->createJobExecution([
                'user' => 'julia',
                'job_instance_id' => $jobInstances['find_job_user_test']
            ]),
        ];
    }

    public function test_it_find_job_users(): void
    {
        $findJobUsersQuery = $this->get('Akeneo\Platform\Job\Domain\Query\FindJobUsersInterface');

        $expectedJobUsers = [
            'admin',
            'julia',
        ];

        $this->assertEqualsCanonicalizing($expectedJobUsers, $findJobUsersQuery->visible());
    }
}
