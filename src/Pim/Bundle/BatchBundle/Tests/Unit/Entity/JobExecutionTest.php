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
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobExecutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Job $job
     */
    protected $jobExecution;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->jobExecution = new JobExecution();
    }

    public function testGetId()
    {
        $this->assertNull($this->jobExecution->getId());
    }

    public function testGetSetEndTime()
    {
        $this->assertNull($this->jobExecution->getEndTime());

        $expectedEndTime = new \DateTime();
        $this->assertEntity($this->jobExecution->setEndTime($expectedEndTime));
        $this->assertEquals($expectedEndTime, $this->jobExecution->getEndTime());
    }

    public function testGetSetStartTime()
    {
        $this->assertNull($this->jobExecution->getStartTime());

        $expectedStartTime = new \DateTime();
        $this->assertEntity($this->jobExecution->setStartTime($expectedStartTime));
        $this->assertEquals($expectedStartTime, $this->jobExecution->getStartTime());
    }

    public function testGetSetCreateTime()
    {
        $this->assertNotNull($this->jobExecution->getCreateTime());

        $expectedCreateTime = new \DateTime();
        $this->assertEntity($this->jobExecution->setCreateTime($expectedCreateTime));
        $this->assertEquals($expectedCreateTime, $this->jobExecution->getCreateTime());
    }

    public function testGetSetUpdatedTime()
    {
        $this->assertNull($this->jobExecution->getUpdatedTime());

        $expectedUpdatedTime = new \DateTime();
        $this->assertEntity($this->jobExecution->setUpdatedTime($expectedUpdatedTime));
        $this->assertEquals($expectedUpdatedTime, $this->jobExecution->getUpdatedTime());
    }

    public function testGetSetStatus()
    {
        $this->assertEquals(new BatchStatus(BatchStatus::STARTING), $this->jobExecution->getStatus());

        $expectedBatchStatus = new BatchStatus(BatchStatus::COMPLETED);

        $this->assertEntity($this->jobExecution->setStatus($expectedBatchStatus));
        $this->assertEquals($expectedBatchStatus, $this->jobExecution->getStatus());
    }

    public function testUpgradeStatus()
    {
        $expectedBatchStatus = new BatchStatus(BatchStatus::STARTED);
        $this->jobExecution->setStatus($expectedBatchStatus);

        $expectedBatchStatus->upgradeTo(BatchStatus::COMPLETED);

        $this->assertEntity($this->jobExecution->upgradeStatus(BatchStatus::COMPLETED));
        $this->assertEquals($expectedBatchStatus, $this->jobExecution->getStatus());
    }

    public function testGetSetExitStatus()
    {
        $this->assertEquals(new ExitStatus(ExitStatus::UNKNOWN), $this->jobExecution->getExitStatus());

        $expectedExitStatus = new ExitStatus(ExitStatus::COMPLETED);

        $this->assertEntity($this->jobExecution->setExitStatus($expectedExitStatus));
        $this->assertEquals($expectedExitStatus, $this->jobExecution->getExitStatus());
    }

    public function testGetSetExecutionContext()
    {
        $this->assertNull($this->jobExecution->getExecutionContext());

        $expectedExecutionContext = new ExecutionContext();
        $this->assertEntity($this->jobExecution->setExecutionContext($expectedExecutionContext));
        $this->assertEquals($expectedExecutionContext, $this->jobExecution->getExecutionContext());
    }

    public function testStepExecutions()
    {
        $this->assertEmpty($this->jobExecution->getStepExecutions());

        $jobExecution = new JobExecution();

        $stepExecution1 = new StepExecution('my_step_name_1', $jobExecution);
        $this->jobExecution->addStepExecution($stepExecution1);

        $this->assertEquals(array($stepExecution1), $this->jobExecution->getStepExecutions());

        $stepExecution2 = $this->jobExecution->createStepExecution('my_step_name_2');

        $this->assertEquals(array($stepExecution1, $stepExecution2), $this->jobExecution->getStepExecutions());
    }

    public function testIsRunning()
    {
        $this->assertFalse($this->jobExecution->isRunning());
        $this->jobExecution->setStartTime(new \DateTime());
        $this->assertTrue($this->jobExecution->isRunning());
        $this->jobExecution->setEndTime(new \DateTime());
        $this->assertFalse($this->jobExecution->isRunning());
    }

    public function testIsStopping()
    {
        $this->assertFalse($this->jobExecution->isStopping());
        $this->jobExecution->upgradeStatus(BatchStatus::STOPPING);
        $this->assertTrue($this->jobExecution->isStopping());
    }

    public function testStop()
    {
        $this->assertFalse($this->jobExecution->isStopping());
        $this->jobExecution->stop();
        $this->assertTrue($this->jobExecution->isStopping());
    }

    public function testStopWithStepExecutions()
    {
        $this->assertFalse($this->jobExecution->isStopping());
        $this->jobExecution->createStepExecution('my_step_name_2');
        $this->assertEntity($this->jobExecution->stop());
        $this->assertTrue($this->jobExecution->isStopping());
    }

    public function testGetAddFailureExceptions()
    {
        $this->assertEmpty($this->jobExecution->getFailureExceptions());
        $this->assertEmpty($this->jobExecution->getAllFailureExceptions());
        $stepExecution = $this->jobExecution->createStepExecution('my_step_name_2');
        $this->assertEmpty($this->jobExecution->getAllFailureExceptions());

        $exception1 = new \Exception('My exception 1');
        $exception2 = new \Exception('My exception 2');
        $stepException = new \Exception('My step exception 1');

        $this->assertEntity($this->jobExecution->addFailureException($exception1));

        $this->assertEquals(array($exception1), $this->jobExecution->getFailureExceptions());
        $this->assertEquals(array($exception1), $this->jobExecution->getAllFailureExceptions());

        $this->assertEntity($this->jobExecution->addFailureException($exception2));

        $this->assertEquals(array($exception1, $exception2), $this->jobExecution->getFailureExceptions());
        $this->assertEquals(array($exception1, $exception2), $this->jobExecution->getAllFailureExceptions());

        $stepExecution->addFailureException($stepException);

        $this->assertEquals(array($exception1, $exception2), $this->jobExecution->getFailureExceptions());
        $this->assertEquals(array($exception1, $exception2, $stepException), $this->jobExecution->getAllFailureExceptions());
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Entity\JobExecution', $entity);
    }
}
