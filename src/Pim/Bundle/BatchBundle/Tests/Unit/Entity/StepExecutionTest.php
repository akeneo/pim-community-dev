<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Entity;

use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\BatchBundle\Item\ExecutionContext;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

/**
 * Test related class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class StepExecutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StepExecution 
     */
    protected $stepExecution;

    /**
     * @var JobExecution
     */
    protected $jobExecution;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->jobExecution = new JobExecution();
        $this->stepExecution = new StepExecution('my_step_execution',$this->jobExecution);
    }

    public function testGetId()
    {
        $this->assertNull($this->stepExecution->getId());
    }

    public function testGetSetEndTime()
    {
        $this->assertNull($this->stepExecution->getEndTime());

        $expectedEndTime = new \DateTime();
        $this->assertEntity($this->stepExecution->setEndTime($expectedEndTime));
        $this->assertEquals($expectedEndTime, $this->stepExecution->getEndTime());
    }

    public function testGetSetStartTime()
    {
        $afterConstruct = new \DateTime();
        $this->assertGreaterThanOrEqual($afterConstruct, $this->stepExecution->getStartTime());

        $expectedStartTime = new \DateTime();
        $this->assertEntity($this->stepExecution->setStartTime($expectedStartTime));
        $this->assertEquals($expectedStartTime, $this->stepExecution->getStartTime());
    }

    public function testGetSetStatus()
    {
        $this->assertEquals(new BatchStatus(BatchStatus::STARTING), $this->stepExecution->getStatus());

        $expectedBatchStatus = new BatchStatus(BatchStatus::COMPLETED);

        $this->assertEntity($this->stepExecution->setStatus($expectedBatchStatus));
        $this->assertEquals($expectedBatchStatus, $this->stepExecution->getStatus());
    }

    public function testUpgradeStatus()
    {
        $expectedBatchStatus = new BatchStatus(BatchStatus::STARTED);
        $this->stepExecution->setStatus($expectedBatchStatus);

        $expectedBatchStatus->upgradeTo(BatchStatus::COMPLETED);

        $this->assertEntity($this->stepExecution->upgradeStatus(BatchStatus::COMPLETED));
        $this->assertEquals($expectedBatchStatus, $this->stepExecution->getStatus());
    }

    public function testGetSetExitStatus()
    {
        $this->assertEquals(new ExitStatus(ExitStatus::EXECUTING), $this->stepExecution->getExitStatus());

        $expectedExitStatus = new ExitStatus(ExitStatus::COMPLETED);

        $this->assertEntity($this->stepExecution->setExitStatus($expectedExitStatus));
        $this->assertEquals($expectedExitStatus, $this->stepExecution->getExitStatus());
    }

    public function testGetSetExecutionContext()
    {
        $this->assertNull($this->stepExecution->getExecutionContext());

        $expectedExecutionContext = new ExecutionContext();
        $this->assertEntity($this->stepExecution->setExecutionContext($expectedExecutionContext));
        $this->assertSame($expectedExecutionContext, $this->stepExecution->getExecutionContext());
    }

    public function testGetAddFailureExceptions()
    {
        $this->assertEmpty($this->stepExecution->getFailureExceptions());

        $exception1 = new \Exception('My exception 1');
        $exception2 = new \Exception('My exception 2');
        $stepException = new \Exception('My step exception 1');

        $this->assertEntity($this->stepExecution->addFailureException($exception1));
        $this->assertEntity($this->stepExecution->addFailureException($exception2));

        $this->assertEquals(array($exception1, $exception2), $this->stepExecution->getFailureExceptions());
    }

    public function testGetSetReadCount()
    {
        $this->assertEquals(0, $this->stepExecution->getReadCount());
        $this->assertEntity($this->stepExecution->setReadCount(8));
        $this->assertEquals(8, $this->stepExecution->getReadCount());
    }

    public function testGetSetWriteCount()
    {
        $this->assertEquals(0, $this->stepExecution->getWriteCount());
        $this->assertEntity($this->stepExecution->setWriteCount(6));
        $this->assertEquals(6, $this->stepExecution->getWriteCount());
    }

    public function testGetSetFilterCount()
    {
        $this->assertEquals(0, $this->stepExecution->getFilterCount());
        $this->assertEntity($this->stepExecution->setFilterCount(5));
        $this->assertEquals(5, $this->stepExecution->getFilterCount());
    }

    public function testTerminateOnly()
    {
        $this->assertFalse($this->stepExecution->isTerminateOnly());
        $this->assertEntity($this->stepExecution->setTerminateOnly());
        $this->assertTrue($this->stepExecution->isTerminateOnly());
    }

    public function testGetStepName()
    {
        $this->assertEquals('my_step_execution', $this->stepExecution->getStepName());
    }

    public function testGetJobExecution()
    {
        $this->assertSame($this->jobExecution, $this->stepExecution->getJobExecution());
    }

    public function testToString()
    {
        $expectedString = "id=0, name=my_step_execution, status=2, exitStatus=EXECUTING, readCount=0, filterCount=0, writeCount=0 readSkipCount=0, writeSkipCount=0, processSkipCount=0";
        $this->assertEquals($expectedString, (string) $this->stepExecution);
    }


    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Entity\StepExecution', $entity);
    }
}
