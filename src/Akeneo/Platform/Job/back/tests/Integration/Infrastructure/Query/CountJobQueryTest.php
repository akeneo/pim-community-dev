<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\JobParameters;

class CountJobQueryTest extends IntegrationTestCase
{
    private DoctrineJobRepository $jobExecutionRepository;
    private JobInstanceRepository $jobInstanceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jobExecutionRepository = $this->get('akeneo_batch.job_repository');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
    }

    public function test_it_counts_job(): void
    {
        $this->loadJobExecutions();
        $countJobQuery = $this->get('Akeneo\Platform\Job\Domain\Query\CountJobQueryInterface');
        $this->assertEquals(1, $countJobQuery->all());
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadJobExecutions(): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('edit_common_attributes');
        $this->jobExecutionRepository->createJobExecution($jobInstance, new JobParameters([]));
    }
}
