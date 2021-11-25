<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class FindJobUsersTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $jobInstances = [
            'a_product_import' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'a_product_import',
                'job_name' => 'a_product_import',
                'label' => 'a_product_import',
                'type' => 'import',
            ]),
            'a_not_visible_job' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'prepare_evaluation',
                'job_name' => 'prepare_evaluation',
                'label' => 'prepare_evaluation',
                'type' => 'data_quality_insights',
            ]),
        ];

        $this->fixturesJobHelper->createJobExecution([
            'user' => 'julia',
            'job_instance_id' => $jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'julia',
            'job_instance_id' => $jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'admin',
            'job_instance_id' => $jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'bob',
            'job_instance_id' => $jobInstances['a_not_visible_job'],
            'is_visible' => false,
        ]);
    }

    public function test_it_find_job_users(): void
    {
        $findJobUsersQuery = $this->get('Akeneo\Platform\Job\Domain\Query\FindJobUsersInterface');

        $expectedJobUsers = [
            'julia',
            'admin',
        ];

        $this->assertEqualsCanonicalizing($expectedJobUsers, $findJobUsersQuery->search(1));
    }
}
