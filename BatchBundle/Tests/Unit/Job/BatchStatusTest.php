<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Job;

use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

/**
 * Tests related to the BatchStatus class
 *
 */
class BatchStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $this->assertEquals("ABANDONED", new BatchStatus(BatchStatus::ABANDONED));
    }

    public function testSetValue()
    {
        $batchStatus = new BatchStatus(BatchStatus::UNKNOWN);
        $batchStatus->setValue(BatchStatus::FAILED);

        $this->assertEquals(BatchStatus::FAILED, $batchStatus->getValue());
    }

    public function testMaxStatus()
    {
        $this->assertEquals(
            BatchStatus::FAILED,
            BatchStatus::max(BatchStatus::FAILED, BatchStatus::COMPLETED)
        );

        $this->assertEquals(
            BatchStatus::FAILED,
            BatchStatus::max(BatchStatus::COMPLETED, BatchStatus::FAILED)
        );

        $this->assertEquals(
            BatchStatus::FAILED,
            BatchStatus::max(BatchStatus::FAILED, BatchStatus::FAILED)
        );

        $this->assertEquals(
            BatchStatus::STARTED,
            BatchStatus::max(BatchStatus::STARTED, BatchStatus::STARTING)
        );

        $this->assertEquals(
            BatchStatus::STARTED,
            BatchStatus::max(BatchStatus::COMPLETED, BatchStatus::STARTED)
        );
    }

    public function testUpgradeStatusFinished()
    {
        $failed = new BatchStatus(BatchStatus::FAILED);

        $this->assertEquals(
            new BatchStatus(BatchStatus::FAILED),
            $failed->upgradeTo(BatchStatus::COMPLETED)
        );

        $completed = new BatchStatus(BatchStatus::COMPLETED);
        $this->assertEquals(
            new BatchStatus(BatchStatus::FAILED),
            $completed->upgradeTo(BatchStatus::FAILED)
        );
    }

    public function testUpgradeStatusUnfinished()
    {
        $starting = new BatchStatus(BatchStatus::STARTING);
        $this->assertEquals(
            new BatchStatus(BatchStatus::COMPLETED),
            $starting->upgradeTo(BatchStatus::COMPLETED)
        );

        $completed = new BatchStatus(BatchStatus::COMPLETED);
        $this->assertEquals(
            new BatchStatus(BatchStatus::COMPLETED),
            $completed->upgradeTo(BatchStatus::STARTING)
        );

        $starting = new BatchStatus(BatchStatus::STARTING);
        $this->assertEquals(
            new BatchStatus(BatchStatus::STARTED),
            $starting->upgradeTo(BatchStatus::STARTED)
        );

        $started = new BatchStatus(BatchStatus::STARTED);
        $this->assertEquals(
            new BatchStatus(BatchStatus::STARTED),
            $started->upgradeTo(BatchStatus::STARTING)
        );
    }

    public function testIsRunning()
    {
        $failed = new BatchStatus(BatchStatus::FAILED);
        $this->assertFalse($failed->isRunning());

        $completed = new  BatchStatus(BatchStatus::COMPLETED);
        $this->assertFalse($completed->isRunning());

        $started = new BatchStatus(BatchStatus::STARTED);
        $this->assertTrue($started->isRunning());

        $starting = new BatchStatus(BatchStatus::STARTING);
        $this->assertTrue($starting->isRunning());
    }

    public function testIsUnsuccessful()
    {
        $failed = new BatchStatus(BatchStatus::FAILED);
        $this->assertTrue($failed->isUnsuccessful());

        $completed = new BatchStatus(BatchStatus::COMPLETED);
        $this->assertFalse($completed->isUnsuccessful());

        $started = new BatchStatus(BatchStatus::STARTED);
        $this->assertFalse($started->isUnsuccessful());

        $starting = new BatchStatus(BatchStatus::STARTING);
        $this->assertFalse($starting->isUnsuccessful());
    }

    public function testGetAllLabels()
    {
        $expectedLabels = array(
            BatchStatus::COMPLETED => 'COMPLETED',
            BatchStatus::STARTING  => 'STARTING',
            BatchStatus::STARTED   => 'STARTED',
            BatchStatus::STOPPING  => 'STOPPING',
            BatchStatus::STOPPED   => 'STOPPED',
            BatchStatus::FAILED    => 'FAILED',
            BatchStatus::ABANDONED => 'ABANDONED',
            BatchStatus::UNKNOWN   => 'UNKNOWN'
        );

        $this->assertEquals($expectedLabels, BatchStatus::getAllLabels());
    }
}
