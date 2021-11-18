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
            'a_product_import' => $this->fixturesLoader->createJobInstance([
                'code' => 'a_product_import',
                'job_name' => 'a_product_import',
                'label' => 'a_product_import',
                'type' => 'import',
            ]),
            'prepare_evaluation' => $this->fixturesLoader->createJobInstance([
                'code' => 'prepare_evaluation',
                'job_name' => 'prepare_evaluation',
                'label' => 'prepare_evaluation',
                'type' => 'data_quality_insights',
            ]),
        ];

        $this->fixturesLoader->createJobExecution([
            'user' => 'julia',
            'job_instance_id' => $jobInstances['a_product_import']
        ]);
        $this->fixturesLoader->createJobExecution([
            'user' => 'julia',
            'job_instance_id' => $jobInstances['a_product_import']
        ]);
        $this->fixturesLoader->createJobExecution([
            'user' => 'admin',
            'job_instance_id' => $jobInstances['a_product_import']
        ]);
        $this->fixturesLoader->createJobExecution([
            'user' => 'not_visible',
            'job_instance_id' => $jobInstances['prepare_evaluation']
        ]);
    }

    public function test_it_find_job_users(): void
    {
        $findJobUsersQuery = $this->get('Akeneo\Platform\Job\Domain\Query\FindJobUsersInterface');

        $expectedJobUsers = [
            'julia',
            'admin'
        ];

        $this->assertEqualsCanonicalizing($expectedJobUsers, $findJobUsersQuery->visible(1));
    }
}
