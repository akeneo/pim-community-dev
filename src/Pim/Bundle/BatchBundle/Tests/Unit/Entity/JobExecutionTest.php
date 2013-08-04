<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Entity;

use Pim\Bundle\BatchBundle\Entity\JobExecution;
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
