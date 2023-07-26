<?php

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Routing;

use Akeneo\Platform\Job\Application\LaunchJobInstance\GenerateJobExecutionUrlInterface;
use Akeneo\Platform\Job\Infrastructure\Routing\GenerateJobExecutionUrl;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class GenerateJobExecutionUrlTest extends IntegrationTestCase
{
    private GenerateJobExecutionUrl $generateJobExecutionUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generateJobExecutionUrl = $this->get(GenerateJobExecutionUrlInterface::class);
    }

    public function test_it_generates_job_execution_url(): void
    {
        $jobExecutionId = 7;
        $expected = sprintf('#/job/show/%d', $jobExecutionId);

        $this->assertEquals($expected, $this->generateJobExecutionUrl->fromJobExecutionId($jobExecutionId));
    }
}
