<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Manager;

use Akeneo\Bundle\BatchBundle\Job\ExitStatus;
use Akeneo\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

class JobExecutionManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\\ORM\\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCheckRunningStatusCompleted()
    {
        $jobExecution = $this->getMock('Akeneo\\Bundle\\BatchBundle\\Entity\\JobExecution');

        $jobExecution->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::COMPLETED)));

        $jobExecution->expects($this->exactly(2))
            ->method('getExitStatus')
            ->will($this->returnValue(new ExitStatus(ExitStatus::COMPLETED)));

        $jobExecutionManager = new JobExecutionManager($this->entityManager, 'JobExecution');

        $this->assertTrue($jobExecutionManager->checkRunningStatus($jobExecution));
    }

    public function testCheckRunningStatusRunning()
    {
        $jobExecution = $this->getMock('Akeneo\\Bundle\\BatchBundle\\Entity\\JobExecution');

        $jobExecution->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTED)));

        $jobExecution->expects($this->once())
            ->method('getExitStatus')
            ->will($this->returnValue(new ExitStatus(ExitStatus::UNKNOWN)));

        $jobExecution->expects($this->once())
            ->method('getPid')
            ->will($this->returnValue(1));

        $jobExecutionManager = new JobExecutionManager($this->entityManager);

        $this->assertTrue($jobExecutionManager->checkRunningStatus($jobExecution));
    }

    public function testCheckRunningStatusKilled()
    {
        $jobExecution = $this->getMock('Akeneo\\Bundle\\BatchBundle\\Entity\\JobExecution');

        $jobExecution->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::ABANDONED)));

        $jobExecution->expects($this->once())
            ->method('getExitStatus')
            ->will($this->returnValue(new ExitStatus(ExitStatus::UNKNOWN)));

        $jobExecution->expects($this->once())
            ->method('getPid')
            ->will($this->returnValue(10000));

        $jobExecutionManager = new JobExecutionManager($this->entityManager);

        $this->assertFalse($jobExecutionManager->checkRunningStatus($jobExecution));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCheckRunningStatusInvalidPid()
    {
        $jobExecution = $this->getMock('Akeneo\\Bundle\\BatchBundle\\Entity\\JobExecution');

        $jobExecution->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTED)));

        $jobExecution->expects($this->once())
            ->method('getExitStatus')
            ->will($this->returnValue(new ExitStatus(ExitStatus::UNKNOWN)));

        $jobExecution->expects($this->once())
            ->method('getPid')
            ->will($this->returnValue(null));

        $jobExecutionManager = new JobExecutionManager($this->entityManager);

        $jobExecutionManager->checkRunningStatus($jobExecution);
    }

    public function testMarkAsFailed()
    {
        $jobExecution = $this->getMock('Akeneo\\Bundle\\BatchBundle\\Entity\\JobExecution');

        $jobExecution->expects($this->once())
            ->method('setStatus');
        $jobExecution->expects($this->once())
            ->method('setExitStatus');
        $jobExecution->expects($this->once())
            ->method('setEndTime');
        $jobExecution->expects($this->once())
            ->method('addFailureException');

        $jobExecutionManager = new JobExecutionManager($this->entityManager);

        $jobExecutionManager->markAsFailed($jobExecution);
    }
}
