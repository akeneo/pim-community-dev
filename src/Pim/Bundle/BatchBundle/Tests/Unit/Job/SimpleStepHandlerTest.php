<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Pim\Bundle\BatchBundle\Job\SimpleStepHandler;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\JobInterruptedException;
use Pim\Bundle\BatchBundle\Item\ExecutionContext;
use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\BatchBundle\Tests\Unit\Step\InterruptedStep;

/**
 * Tests related to the SimpleStepHandler class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SimpleStepHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $job               = null;
    protected $logger            = null;
    protected $jobRepository     = null;
    protected $simpleStepHandler = null;

    protected function setUp()
    {
        $this->logger = new Logger('JobLogger');
        $this->logger->pushHandler(new TestHandler());

        $this->jobRepository = new MockJobRepository();

        $this->simpleStepHandler = new SimpleStepHandler(
            $this->logger,
            $this->jobRepository
        );
    }

    public function testConstructExecutionContextNull()
    {
        $simpleStepHandler = new SimpleStepHandler(
            $this->logger,
            $this->jobRepository
        );

        $this->assertNotNull($simpleStepHandler->getExecutionContext());
    }

    public function testConstructExecutionContextNotNull()
    {
        $executionContext = new ExecutionContext();

        $simpleStepHandler = new SimpleStepHandler(
            $this->logger,
            $this->jobRepository,
            $executionContext
        );

        $this->assertSame($executionContext, $simpleStepHandler->getExecutionContext());
    }

    public function testSetLogger()
    {
        $this->assertEntity($this->simpleStepHandler->setLogger($this->logger));
    }

    public function testSetJobRepository()
    {
        $this->assertEntity($this->simpleStepHandler->setJobRepository($this->jobRepository));
    }

    public function testGetSetExecutionContext()
    {
        $executionContext = new ExecutionContext();
        
        $this->assertEntity($this->simpleStepHandler->setExecutionContext($executionContext));
        $this->assertSame($executionContext, $this->simpleStepHandler->getExecutionContext());
    }

    public function testHandleStep()
    {
        $step = $this->getMock('Pim\\Bundle\\BatchBundle\\Step\\StepInterface');
        $step->expects($this->once())
            ->method('execute');

        $jobExecution = new JobExecution();

        $this->assertInstanceOf(
            'Pim\\Bundle\\BatchBundle\\Entity\\StepExecution',
            $this->simpleStepHandler->handleStep($step, $jobExecution)
        );
    }

    /**
     * @expectedException Pim\Bundle\BatchBundle\Job\JobInterruptedException
     */
    public function testHandleStepJobStopping()
    {
        $step = $this->getMock('Pim\\Bundle\\BatchBundle\\Step\\StepInterface');
        $step->expects($this->never())
            ->method('execute');

        $jobExecution = new JobExecution();
        $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));

        $this->simpleStepHandler->handleStep($step, $jobExecution);
    }

    public function testHandleStepStepInterrupted()
    {
        $step = new InterruptedStep('my_interrupted_step');
        $step->setLogger($this->logger);
        $step->setJobRepository($this->jobRepository);

        $jobExecution = new JobExecution();

        try {
            $this->simpleStepHandler->handleStep($step, $jobExecution);
            $this->assertFalse(true, "We shouldn't get there, a JobInterruptedException should have been threw");
        } catch (JobInterruptedException $e) {
        }

        $this->assertEquals(BatchStatus::STOPPING, $jobExecution->getStatus()->getValue());
    }

    public function testHandleStepWithStoppedStepExecution()
    {
        $step = $this->getMock('Pim\\Bundle\\BatchBundle\\Step\\StepInterface');
        $step->expects($this->once())
            ->method('execute');


        $jobExecution = $this->getMock('Pim\\Bundle\\BatchBundle\\Entity\JobExecution');

        $stoppedStepExecution = new StepExecution('my_step_name', $jobExecution);
        $stoppedStepExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));

        $jobExecution->expects($this->once())
            ->method('createStepExecution')
            ->will($this->returnValue($stoppedStepExecution));

        try {
            $this->simpleStepHandler->handleStep($step, $jobExecution);
            $this->assertFalse(true, "We shouldn't get there, a JobInterruptedException should have been threw");
        } catch (JobInterruptedException $e) {
        }

        $this->markTestIncomplete();
        //Fixme, the mock object should not mocked the setStatus() method
        //$this->assertEquals(BatchStatus::STOPPING, $jobExecution->getStatus()->getValue());
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Job\SimpleStepHandler', $entity);
    }
}
