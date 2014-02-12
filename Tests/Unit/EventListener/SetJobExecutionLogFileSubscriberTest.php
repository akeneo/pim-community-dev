<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\EventListener;

use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\EventListener\SetJobExecutionLogFileSubscriber;

/**
 * Test related class
 */
class SetJobExecutionLogFileSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->logger = $this->getLoggerMock();
        $this->subscriber = new SetJobExecutionLogFileSubscriber($this->logger);
    }

    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            array(
                EventInterface::BEFORE_JOB_EXECUTION => 'setJobExecutionLogFile',
            ),
            SetJobExecutionLogFileSubscriber::getSubscribedEvents()
        );
    }

    public function testSetJobExecutionLogFile()
    {
        $this->logger
            ->expects($this->any())
            ->method('getFileName')
            ->will($this->returnValue('/tmp/foo.log'));

        $jobExecution = $this->getJobExecutionMock();
        $jobExecution->expects($this->once())
            ->method('setLogFile')
            ->with('/tmp/foo.log');

        $event = $this->getJobExecutionEventMock($jobExecution);
        $this->subscriber->setJobExecutionLogFile($event);
    }

    private function getLoggerMock()
    {
        return $this
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getJobExecutionEventMock($jobExecution = null)
    {
        $event = $this
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getJobExecution')
            ->will($this->returnValue($jobExecution));

        return $event;
    }

    private function getJobExecutionMock()
    {
        return $this
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
