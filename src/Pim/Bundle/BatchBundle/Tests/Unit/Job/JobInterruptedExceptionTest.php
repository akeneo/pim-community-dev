<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Pim\Bundle\BatchBundle\Step\ItemStep;
use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BatchBundle\Job\JobInterruptedException;
use Pim\Bundle\BatchBundle\Entity\Job as JobInstance;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;
use Pim\Bundle\BatchBundle\Job\SimpleStepHandler;
use Pim\Bundle\BatchBundle\Tests\Unit\Step\InterruptedStep;
use Pim\Bundle\BatchBundle\Tests\Unit\Step\IncompleteStep;
use Pim\Bundle\BatchBundle\Tests\Unit\Job\MockJobRepository;

/**
 * Tests related to the JobInterruptedException
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobInterruptedExceptionTest extends \PHPUnit_Framework_TestCase
{
    protected $jobInterruptedException = null;

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
