<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class CountJobExecutionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturesLoader->loadFixtures();
    }

    public function test_it_counts_job(): void
    {
        $countJobQuery = $this->get('Akeneo\Platform\Job\Domain\Query\CountJobExecutionInterface');

        $this->assertEquals(1, $countJobQuery->all());

        $jobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_new_product_import',
            'job_name' => 'a_new_product_import',
            'label' => 'a_new_product_import',
            'type' => 'import',
        ]);
        $this->fixturesJobHelper->createJobExecution(['job_instance_id' => $jobInstanceId]);

        $this->assertEquals(2, $countJobQuery->all());
    }
}
