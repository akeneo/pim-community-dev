<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\EventListener;

use Pim\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BatchBundle\EventListener\SetJobExecutionLogFileSubscriber;

/**
 * Test related class
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                EventInterface::BEFORE_JOB_EXECUTION => 'beforeJobExecution',
            ),
            SetJobExecutionLogFileSubscriber::getSubscribedEvents()
        );
    }

    public function testBeforeJobExecution()
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
        $this->subscriber->beforeJobExecution($event);
    }

    private function getLoggerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getJobExecutionEventMock($jobExecution = null)
    {
        $event = $this
            ->getMockBuilder('Pim\Bundle\BatchBundle\Event\JobExecutionEvent')
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
            ->getMockBuilder('Pim\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
