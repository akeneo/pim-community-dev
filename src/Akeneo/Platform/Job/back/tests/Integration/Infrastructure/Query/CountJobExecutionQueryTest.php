<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class CountJobExecutionQueryTest extends IntegrationTestCase
{
    private array $fixtures = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = $this->fixturesLoader->loadProductImportFixtures();
    }

    public function test_it_counts_job(): void
    {
        $countJobQuery = $this->get('Akeneo\Platform\Job\Domain\Query\CountJobExecutionQueryInterface');

        $this->assertEquals(1, $countJobQuery->all());
        $this->fixturesLoader->createJobExecution(['job_instance_id' => $this->fixtures['job_instances']['another_product_import']]);
        $this->assertEquals(2, $countJobQuery->all());
    }
}
