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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $jobExecution = new JobExecution();
        $this->stepExecution = new StepExecution('my_step_execution',$jobExecution);
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
        $this->assertEquals($expectedExecutionContext, $this->stepExecution->getExecutionContext());
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
