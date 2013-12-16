<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\Job;

use Oro\Bundle\BatchBundle\Step\ItemStep;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\Job;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\BatchBundle\Job\ExitStatus;
use Oro\Bundle\BatchBundle\Tests\Unit\Step\InterruptedStep;
use Oro\Bundle\BatchBundle\Tests\Unit\Step\IncompleteStep;

/**
 * Tests related to the Job class
 *
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    const JOB_TEST_NAME = 'job_test';

    protected $job             = null;
    protected $jobRepository   = null;
    protected $eventDispatcher = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->jobRepository   = $this->getMock('Oro\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');

        $this->job = new Job(self::JOB_TEST_NAME);
        $this->job->setEventDispatcher($this->eventDispatcher);
        $this->job->setJobRepository($this->jobRepository);
    }

    public function testGetName()
    {
        $this->assertEquals(self::JOB_TEST_NAME, $this->job->getName());
    }

    public function testSetName()
    {
        $this->job->setName('mynewname');
        $this->assertEquals('mynewname', $this->job->getName());
    }

    public function testExecute()
    {
        $beforeExecute = new \DateTime();

        $jobInstance = new JobInstance('test_connector', JobInstance::TYPE_IMPORT, 'test_job_instance');

        $jobExecution = new JobExecution($jobInstance);

        $this->assertNull($jobExecution->getStartTime());
        $this->assertNull($jobExecution->getEndTIme());
        $this->assertEquals(BatchStatus::STARTING, $jobExecution->getStatus()->getValue(), 'Batch status starting');

        $this->job->setJobRepository($this->jobRepository);
        $this->job->execute($jobExecution);

        $this->assertGreaterThanOrEqual(
            $beforeExecute,
            $jobExecution->getStartTime(),
            'Start time after test beginning'
        );
        $this->assertGreaterThanOrEqual(
            $beforeExecute,
            $jobExecution->getEndTime(),
            'End time after test beginning'
        );
        $this->assertGreaterThanOrEqual(
            $jobExecution->getEndTime(),
            $jobExecution->getStartTime(),
            'End time after start time'
        );
        // No step executed, must be not completed
        $this->assertEquals(BatchStatus::STARTED, $jobExecution->getStatus()->getValue(), 'Batch status started');
    }

    public function testExecuteException()
    {
        $exception = new \Exception('My test exception');

        $jobInstance = new JobInstance('test_connector', JobInstance::TYPE_IMPORT, 'test_job_instance');
        $jobExecution = new JobExecution($jobInstance);

        $mockStep = $this->getMockForAbstractClass(
            'Oro\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array('my_mock_step')
        );

        $mockStep->setEventDispatcher($this->eventDispatcher);
        $mockStep->setJobRepository($this->jobRepository);
        $mockStep->expects($this->any())
            ->method('doExecute')
            ->will($this->throwException($exception));

        $this->job->addStep('name', $mockStep);

        $this->job->execute($jobExecution);

        $this->assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue(), 'Batch status failed');
        $this->assertEquals(
            ExitStatus::FAILED,
            $jobExecution->getExitStatus()->getExitCode(),
            'Exit status code stopped'
        );
        $this->assertStringStartsWith(
            $exception->getTraceAsString(),
            $jobExecution->getExitStatus()->getExitDescription(),
            'Exit description'
        );
    }

    public function testExecuteStoppingWithNoStep()
    {
        $jobInstance = new JobInstance('test_connector', JobInstance::TYPE_IMPORT, 'test_job_instance');
        $jobExecution = new JobExecution($jobInstance);
        $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));

        $this->job->setJobRepository($this->jobRepository);
        $this->job->execute($jobExecution);

        $this->assertNull($jobExecution->getStartTime());
        $this->assertEquals(BatchStatus::STOPPED, $jobExecution->getStatus()->getValue(), 'Batch status stopped');
        $this->assertEquals(ExitStatus::NOOP, $jobExecution->getExitStatus()->getExitCode(), 'Exit status completed');
    }

    public function testExecuteInterrupted()
    {
        $jobExecution = new JobExecution();

        $step = new InterruptedStep('my_interrupted_step');
        $step->setEventDispatcher($this->eventDispatcher);
        $step->setJobRepository($this->jobRepository);

        $this->job->setJobRepository($this->jobRepository);
        $this->job->addStep('name', $step);
        $this->job->execute($jobExecution);

        $this->assertEquals(BatchStatus::STOPPED, $jobExecution->getStatus()->getValue(), 'Batch status stopped');
        $this->assertEquals(
            ExitStatus::STOPPED,
            $jobExecution->getExitStatus()->getExitCode(),
            'Exit status code stopped'
        );
        $this->assertStringStartsWith(
            'Oro\Bundle\BatchBundle\Job\JobInterruptedException',
            $jobExecution->getExitStatus()->getExitDescription(),
            'Exit description'
        );

    }

    public function testExecuteIncomplete()
    {
        $jobExecution = new JobExecution();

        $step = new IncompleteStep('my_incomplete_step');
        $step->setEventDispatcher($this->eventDispatcher);
        $step->setJobRepository($this->jobRepository);

        $this->job->setJobRepository($this->jobRepository);
        $this->job->addStep('name', $step);
        $this->job->execute($jobExecution);

        $this->assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue(), 'Batch status stopped');

        $this->assertEquals(
            ExitStatus::COMPLETED,
            $jobExecution->getExitStatus()->getExitCode(),
            'Exit status code stopped'
        );
    }

    public function testToString()
    {
        $this->assertEquals(
            get_class($this->job).': [name='.self::JOB_TEST_NAME.']',
            (string) $this->job
        );
    }

    public function testGetConfiguration()
    {
        $expectedConfiguration = array(
            'reader_foo' => 'bar',
            'processor_foo' => 'bar',
            'writer_foo' => 'bar',
        );
        $reader    = $this->getReaderMock($expectedConfiguration, array('reader_foo'));
        $processor = $this->getProcessorMock($expectedConfiguration, array('processor_foo'));
        $writer    = $this->getWriterMock($expectedConfiguration, array('writer_foo'));

        $step = $this->getItemStep('export', $reader, $processor, $writer);

        $this->job->addStep('name', $step);

        $this->assertEquals($expectedConfiguration, $this->job->getConfiguration());
    }

    public function testSetConfiguration()
    {
        $config =array(
            'reader_foo' => 'reader_bar',
            'processor_foo' => 'processor_bar',
            'writer_foo' => 'writer_bar',
        );

        $reader    = $this->getReaderMock(array(), array('reader_foo'));
        $processor = $this->getProcessorMock(array(), array('processor_foo'));
        $writer    = $this->getWriterMock(array(), array('writer_foo'));

        $reader->expects($this->once())
            ->method('setConfiguration')
            ->with($config);

        $processor->expects($this->once())
            ->method('setConfiguration')
            ->with($config);

        $writer->expects($this->once())
            ->method('setConfiguration')
            ->with($config);

        $itemStep = $this->getItemStep('export', $reader, $processor, $writer);
        $this->job->addStep('name', $itemStep);
        $this->job->setConfiguration($config);
    }

    public function testAddStep()
    {
        $mockStep1 = $this->getMockForAbstractClass(
            'Oro\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array('my_mock_step1')
        );
        $mockStep2 = $this->getMockForAbstractClass(
            'Oro\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array('my_mock_step2')
        );

        $this->job->addStep('name1', $mockStep1);
        $this->job->addStep('name2', $mockStep2);

        $this->assertEquals(array($mockStep1, $mockStep2), $this->job->getSteps());
    }

    public function testSetSteps()
    {
        $mockStep1 = $this->getMockForAbstractClass(
            'Oro\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array('my_mock_step1')
        );
        $mockStep2 = $this->getMockForAbstractClass(
            'Oro\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array('my_mock_step2')
        );

        $this->job->setSteps(array($mockStep1, $mockStep2));

        $this->assertEquals(array($mockStep1, $mockStep2), $this->job->getSteps());
    }

    public function testGetStepNames()
    {
        $mockStep1 = $this->getMockForAbstractClass(
            'Oro\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array('my_mock_step1')
        );
        $mockStep2 = $this->getMockForAbstractClass(
            'Oro\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array('my_mock_step2')
        );

        $this->job->setSteps(array($mockStep1, $mockStep2));

        $this->assertEquals(array('my_mock_step1','my_mock_step2'), $this->job->getStepNames());
    }

    public function getItemStep($name, $reader, $processor, $writer)
    {
        $itemStep = new ItemStep($name);

        $itemStep->setReader($reader);
        $itemStep->setProcessor($processor);
        $itemStep->setWriter($writer);

        return $itemStep;
    }

    private function getReaderMock(array $configuration, array $fields = array())
    {
        $reader = $this
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemReaderTestHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));

        $reader->expects($this->any())
            ->method('getConfigurationFields')
            ->will($this->returnValue($fields));

        return $reader;
    }

    private function getProcessorMock(array $configuration, array $fields = array())
    {
        $processor = $this
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemProcessorTestHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $processor->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));

        $processor->expects($this->any())
            ->method('getConfigurationFields')
            ->will($this->returnValue($fields));

        return $processor;
    }

    private function getWriterMock(array $configuration, array $fields = array())
    {
        $writer = $this
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemWriterTestHelper')
            ->getMock();

        $writer->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));

        $writer->expects($this->any())
            ->method('getConfigurationFields')
            ->will($this->returnValue($fields));

        return $writer;
    }
}
