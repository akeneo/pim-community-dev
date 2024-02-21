<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\BatchBundle\tests\Integration\Launcher;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\SynchronousJobLauncher;

class SynchronousJobLauncherIntegration extends TestCase
{
    private SynchronousJobLauncher $jobLauncher;
    private JobInstanceRepository $jobInstanceRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->jobLauncher = $this->get('akeneo_batch.launcher.synchronous_job_launcher');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
    }

    /**
     * @test
     */
    public function it_run_a_job_and_update_health_check_time_in_same_time()
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('clean_removed_attribute_job');

        $jobExecution = $this->jobLauncher->launch($jobInstance, null, ['attribute_codes' => []]);

        $this->assertEquals('COMPLETED', $jobExecution->getStatus());
        $this->assertNotNull($jobExecution->getHealthCheckTime());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
