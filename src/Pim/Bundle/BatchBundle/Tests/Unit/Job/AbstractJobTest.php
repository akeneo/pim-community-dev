<?php
namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;

use Pim\Bundle\BatchBundle\Job\AbstractJob;
use Pim\Bundle\BatchBundle\Job\JobExecution;
use Pim\Bundle\BatchBundle\Job\JobRepository;
use Pim\Bundle\BatchBundle\Job\JobParameters;
use Pim\Bundle\BatchBundle\Job\JobInterruptedException;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

/**
 * Tests related to the AbstractJob class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AbstractJobTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $job = $this->getAbstractJobMock('mytestjob');
        $this->assertEquals('mytestjob', $job->getName());
    }

    public function testSetName()
    {
        $job = $this->getAbstractJobMock('mytestjob');
        $job->setName('mynewname');
        $this->assertEquals('mynewname', $job->getName());
    }

    public function testExecute()
    {
        $beforeExecute = time();
        $job = $this->getAbstractJobMock('mytestjob');

        $jobRepository = new JobRepository();
        $jobParameters = new JobParameters();
        $jobExecution = $jobRepository->createJobExecution($job->getName(), $jobParameters);

        $this->assertEquals(0, $jobExecution->getStartTime());
        $this->assertEquals(0, $jobExecution->getEndTIme());
        $this->assertEquals(BatchStatus::STARTING, $jobExecution->getStatus()->getValue(), 'Batch status starting');

        $job->setJobRepository($jobRepository);
        $job->execute($jobExecution);

        $this->assertGreaterThanOrEqual($beforeExecute, $jobExecution->getStartTime(), 'Start time after test beginning');
        $this->assertGreaterThanOrEqual($beforeExecute, $jobExecution->getEndTime(), 'End time after test beginning');
        $this->assertGreaterThanOrEqual($jobExecution->getEndTime(), $jobExecution->getStartTime(), 'End time after start time');
        // No step executed, must be not completed
        $this->assertEquals(BatchStatus::STARTED, $jobExecution->getStatus()->getValue(), 'Batch status started');
    }

    public function testExecuteException()
    {
        $job = $this->getAbstractJobMock('mytestjob');

        $exception = new \Exception('My test exception');

        $jobRepository = new JobRepository();
        $jobParameters = new JobParameters();
        $jobExecution = $jobRepository->createJobExecution($job->getName(), $jobParameters);

        $job->expects($this->any())
            ->method('doExecute')
            ->will($this->throwException($exception));

        $job->setJobRepository($jobRepository);
        $job->execute($jobExecution);

        $this->assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue(), 'Batch status failed');
        $this->assertEquals(ExitStatus::FAILED, $jobExecution->getExitStatus()->getExitCode(), 'Exit status code stopped');
        $this->assertStringStartsWith(
            $exception->getTraceAsString(),
            $jobExecution->getExitStatus()->getExitDescription(),
            'Exit description'
        );
    }

    public function testExecuteStoppingWithNoStep()
    {
        $job = $this->getAbstractJobMock('mytestjob');

        $jobRepository = new JobRepository();
        $jobParameters = new JobParameters();
        $jobExecution = $jobRepository->createJobExecution($job->getName(), $jobParameters);
        $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));

        $job->setJobRepository($jobRepository);
        $job->execute($jobExecution);

        $this->assertNull($jobExecution->getStartTime());
        $this->assertEquals(BatchStatus::STOPPED, $jobExecution->getStatus()->getValue(), 'Batch status stopped');
        $this->assertEquals(ExitStatus::NOOP, $jobExecution->getExitStatus()->getExitCode(), 'Exit status completed');
    }

    public function testExecuteInterrupted()
    {
        $job = $this->getAbstractJobMock('mytestjob');

        $jobInterruptedException = new JobInterruptedException('My test job interrupted exception');

        $jobRepository = new JobRepository();
        $jobParameters = new JobParameters();
        $jobExecution = $jobRepository->createJobExecution($job->getName(), $jobParameters);

        $job->expects($this->any())
            ->method('doExecute')
            ->will($this->throwException(new JobInterruptedException()));

        $job->setJobRepository($jobRepository);
        $job->execute($jobExecution);

        $this->assertEquals(BatchStatus::STOPPED, $jobExecution->getStatus()->getValue(), 'Batch status stopped');
        $this->assertEquals(ExitStatus::STOPPED, $jobExecution->getExitStatus()->getExitCode(), 'Exit status code stopped');
        $this->assertStringStartsWith(
            'Pim\Bundle\BatchBundle\Job\JobInterruptedException',
            $jobExecution->getExitStatus()->getExitDescription(),
            'Exit description'
        );

    }

    public function testToString()
    {
        $job = $this->getAbstractJobMock('mytestjob');

        $this->assertEquals(
            get_class($job).': [name=mytestjob]',
            (string) $job
        );
    }

    private function getAbstractJobMock($jobName)
    {
        $logger = $this->getLogger();

        return $this->getMockForAbstractClass('Pim\\Bundle\\BatchBundle\\Job\\AbstractJob', array($jobName, $logger));
    }

    private function getLogger()
    {
        $logger = new Logger('JobLogger');
        $logger->pushHandler(new TestHandler());

        return $logger;
    }
}
