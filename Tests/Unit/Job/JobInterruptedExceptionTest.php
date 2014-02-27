<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Job;

use Akeneo\Bundle\BatchBundle\Job\JobInterruptedException;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

/**
 * Tests related to the JobInterruptedException
 *
 */
class JobInterruptedExceptionTest extends \PHPUnit_Framework_TestCase
{
    protected $job;
    protected $jobInterruptedException;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->job = new JobInterruptedException('my_job_interupted_exception');
    }

    public function testStatusNull()
    {
        $jobInterruptedException = new JobInterruptedException('my_job_interupted_exception');
        $this->assertEquals(BatchStatus::STOPPED, $jobInterruptedException->getStatus()->getValue());
    }

    public function testStatus()
    {
        $jobInterruptedException = new JobInterruptedException(
            'my_job_interupted_exception',
            0,
            null,
            new BatchStatus(BatchStatus::COMPLETED)
        );
        $this->assertEquals(BatchStatus::COMPLETED, $jobInterruptedException->getStatus()->getValue());
    }
}
