<?php

namespace Oro\Bundle\CronBundle\Job;

use Symfony\Component\Process\Process;

class DaemonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Daemon
     */
    protected $object;

    /**
     * @var Process
     */
    protected $process;

    protected function setUp()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->markTestSkipped('Unable to run on Windows');
        }

        $this->object = $this->getMockBuilder('Oro\Bundle\CronBundle\Job\Daemon')
            ->setConstructorArgs(array('app', 10))
            ->setMethods(array('getPidProcess', 'getQueueRunProcess', 'getQueueStopProcess'))
            ->getMock();

        $this->process = $this->getMockBuilder('Symfony\Component\Process\Process')
            ->setConstructorArgs(array('echo 1'))
            ->getMock();

        $this->process
            ->expects($this->any())
            ->method('run')
            ->will($this->returnValue(true));

        $this->process
            ->expects($this->any())
            ->method('start')
            ->will($this->returnValue(true));

        $this->object
            ->expects($this->any())
            ->method('getPidProcess')
            ->will($this->returnValue($this->process));

        $this->object
            ->expects($this->any())
            ->method('getQueueRunProcess')
            ->will($this->returnValue($this->process));

        $this->object
            ->expects($this->any())
            ->method('getQueueStopProcess')
            ->with($this->anything())
            ->will($this->returnValue($this->process));
    }

    public function testGetPid()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->markTestSkipped('Unable to run on Windows');
        }

        $this->process
            ->expects($this->once())
            ->method('getOutput')
            ->will($this->returnValue($this->getPsOutput()));

        $this->assertEquals(111, $this->object->getPid());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAlreadyRun()
    {
        $this->process
            ->expects($this->once())
            ->method('getOutput')
            ->will($this->returnValue($this->getPsOutput()));

        $this->object->run();
    }

    public function testFailedRun()
    {
        $this->process
            ->expects($this->any())
            ->method('getOutput')
            ->will($this->returnValue(''));

        $this->assertNull($this->object->run());
    }

    public function testRun()
    {
        $this->process
            ->expects($this->exactly(2))
            ->method('getOutput')
            ->will($this->onConsecutiveCalls('', $this->getPsOutput()));

        $this->assertEquals(111, $this->object->run());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAlreadyStop()
    {
        $this->process
            ->expects($this->once())
            ->method('getOutput')
            ->will($this->returnValue(''));

        $this->object->stop();
    }

    public function testFailedStop()
    {
        $this->process
            ->expects($this->once())
            ->method('getOutput')
            ->will($this->returnValue($this->getPsOutput()));

        $this->process
            ->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(false));

        $this->assertFalse($this->object->stop());
    }

    public function testStop()
    {
        $this->process
            ->expects($this->once())
            ->method('getOutput')
            ->will($this->returnValue($this->getPsOutput()));

        $this->process
            ->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(true));

        $this->assertTrue($this->object->stop());
    }

    protected function getPsOutput()
    {
        return '111 ? S 0:01 php app/console jms-job-queue:run --max-runtime=999999999 --max-concurrent-jobs=5';
    }
}
