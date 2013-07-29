<?php
namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;

use Pim\Bundle\BatchBundle\Tests\Unit\Step\InterruptedStep;

use Pim\Bundle\BatchBundle\Job\Job;
use Pim\Bundle\BatchBundle\Step\ItemStep;
use Pim\Bundle\BatchBundle\Job\JobExecution;
use Pim\Bundle\BatchBundle\Job\JobRepository;
use Pim\Bundle\BatchBundle\Job\JobParameters;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

/**
 * Tests related to the Job class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    const JOB_TEST_NAME = 'job_test';

    protected $job = null;
    protected $logger = null;

    protected function setUp()
    {
        $this->logger = new Logger('JobLogger');
        $this->logger->pushHandler(new TestHandler());

        $this->job = new Job(self::JOB_TEST_NAME);
        $this->job->setLogger($this->logger);
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
        $beforeExecute = time();

        $jobRepository = new JobRepository();
        $jobParameters = new JobParameters();
        $jobExecution = $jobRepository->createJobExecution($this->job->getName(), $jobParameters);

        $this->assertEquals(0, $jobExecution->getStartTime());
        $this->assertEquals(0, $jobExecution->getEndTIme());
        $this->assertEquals(BatchStatus::STARTING, $jobExecution->getStatus()->getValue(), 'Batch status starting');

        $this->job->setJobRepository($jobRepository);
        $this->job->execute($jobExecution);

        $this->assertGreaterThanOrEqual($beforeExecute, $jobExecution->getStartTime(), 'Start time after test beginning');
        $this->assertGreaterThanOrEqual($beforeExecute, $jobExecution->getEndTime(), 'End time after test beginning');
        $this->assertGreaterThanOrEqual($jobExecution->getEndTime(), $jobExecution->getStartTime(), 'End time after start time');
        // No step executed, must be not completed
        $this->assertEquals(BatchStatus::STARTED, $jobExecution->getStatus()->getValue(), 'Batch status started');
    }

    public function testExecuteException()
    {
        $exception = new \Exception('My test exception');

        $jobRepository = new JobRepository();
        $jobParameters = new JobParameters();
        $jobExecution = $jobRepository->createJobExecution($this->job->getName(), $jobParameters);
        $this->job->setJobRepository($jobRepository);

        $mockStep = $this->getMockForAbstractClass('Pim\\Bundle\\BatchBundle\\Step\\AbstractStep', array('my_mock_step'));

        $mockStep->setLogger($this->logger);
        $mockStep->setJobRepository($jobRepository);
        $mockStep->expects($this->any())
            ->method('doExecute')
            ->will($this->throwException($exception));

        $this->job->addStep($mockStep);

        $this->job->execute($jobExecution);

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
        $jobRepository = new JobRepository();
        $jobParameters = new JobParameters();
        $jobExecution = $jobRepository->createJobExecution($this->job->getName(), $jobParameters);
        $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));

        $this->job->setJobRepository($jobRepository);
        $this->job->execute($jobExecution);

        $this->assertNull($jobExecution->getStartTime());
        $this->assertEquals(BatchStatus::STOPPED, $jobExecution->getStatus()->getValue(), 'Batch status stopped');
        $this->assertEquals(ExitStatus::NOOP, $jobExecution->getExitStatus()->getExitCode(), 'Exit status completed');
    }

    public function testExecuteInterrupted()
    {
        $exception = new \Exception('My test exception');

        $jobRepository = new JobRepository();
        $jobParameters = new JobParameters();
        $jobExecution = $jobRepository->createJobExecution($this->job->getName(), $jobParameters);

        $step = new InterruptedStep('my_interrupted_step');
        $step->setLogger($this->logger);
        $step->setJobRepository($jobRepository);

        $this->job->setJobRepository($jobRepository);
        $this->job->addStep($step);
        $this->job->execute($jobExecution);

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
        $this->assertEquals(
            get_class($this->job).': [name='.self::JOB_TEST_NAME.']',
            (string) $this->job
        );
    }

    public function testGetConfiguration()
    {
        $reader    = $this->getReaderMock(array('reader_foo' => 'bar'), array('reader_foo'));
        $processor = $this->getProcessorMock(array('processor_foo' => 'bar'), array('processor_foo'));
        $writer    = $this->getWriterMock(array('writer_foo' => 'bar'), array('writer_foo'));

        $step = $this->getItemStep('export', $reader, $processor, $writer);

        $this->job->addStep($step);
        $expectedConfiguration = array(
            'export' => array(
                'reader' => array(
                    'reader_foo' => 'bar'
                ),
                'processor' => array(
                    'processor_foo' => 'bar'
                ),
                'writer' => array(
                    'writer_foo' => 'bar'
                )
            )
        );

        $this->assertEquals($expectedConfiguration, $this->job->getConfiguration());
    }

    public function testSetConfiguration()
    {
        $reader    = $this->getReaderMock(array(), array('reader_foo'));
        $processor = $this->getProcessorMock(array(), array('processor_foo'));
        $writer    = $this->getWriterMock(array(), array('writer_foo'));

        $reader->expects($this->once())
            ->method('setConfiguration')
            ->with(array('reader_foo' => 'reader_bar'));

        $processor->expects($this->once())
            ->method('setConfiguration')
            ->with(array('processor_foo' => 'processor_bar'));

        $writer->expects($this->once())
            ->method('setConfiguration')
            ->with(array('writer_foo' => 'writer_bar'));

        $itemStep = $this->getItemStep('export', $reader, $processor, $writer);
        $this->job->addStep($itemStep);
        $this->job->setConfiguration(array(
            'export' => array(
                'reader' => array(
                    'reader_foo' => 'reader_bar',
                ),
                'processor' => array(
                    'processor_foo' => 'processor_bar',
                ),
                'writer' => array(
                    'writer_foo' => 'writer_bar',
                )
            )
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetConfigurationWithDifferentSteps()
    {
        $reader    = $this->getReaderMock(array(), array('reader_foo'));
        $processor = $this->getProcessorMock(array(), array('processor_foo'));
        $writer    = $this->getWriterMock(array(), array('writer_foo'));

        $step = $this->getItemStep('export', $reader, $processor, $writer);

        $this->job->addStep($step);
        $this->job->setConfiguration(array(
            'unknown' => array(),
            'export' => array(
                'reader' => array(
                    'reader_foo' => 'reader_bar',
                ),
                'processor' => array(
                    'processor_foo' => 'processor_bar',
                ),
                'writer' => array(
                    'writer_foo' => 'writer_bar',
                )
            )
        ));
    }

    public function testAddStep()
    {
        $mockStep1 = $this->getMockForAbstractClass('Pim\\Bundle\\BatchBundle\\Step\\AbstractStep', array('my_mock_step1'));
        $mockStep2 = $this->getMockForAbstractClass('Pim\\Bundle\\BatchBundle\\Step\\AbstractStep', array('my_mock_step2'));

        $this->job->addStep($mockStep1);
        $this->job->addStep($mockStep2);

        $this->assertEquals(array($mockStep1, $mockStep2), $this->job->getSteps());
    }

    public function testSetSteps()
    {
        $mockStep1 = $this->getMockForAbstractClass('Pim\\Bundle\\BatchBundle\\Step\\AbstractStep', array('my_mock_step1'));
        $mockStep2 = $this->getMockForAbstractClass('Pim\\Bundle\\BatchBundle\\Step\\AbstractStep', array('my_mock_step2'));

        $this->job->setSteps(array($mockStep1, $mockStep2));

        $this->assertEquals(array($mockStep1, $mockStep2), $this->job->getSteps());
    }

    public function testGetStepNames()
    {
        $mockStep1 = $this->getMockForAbstractClass('Pim\\Bundle\\BatchBundle\\Step\\AbstractStep', array('my_mock_step1'));
        $mockStep2 = $this->getMockForAbstractClass('Pim\\Bundle\\BatchBundle\\Step\\AbstractStep', array('my_mock_step2'));

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
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Reader\ProductReader')
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
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Processor\CsvSerializerProcessor')
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
        $writer = $this->getMock('Pim\Bundle\ImportExportBundle\Writer\FileWriter');

        $writer->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));

        $writer->expects($this->any())
            ->method('getConfigurationFields')
            ->will($this->returnValue($fields));

        return $writer;
    }

}
