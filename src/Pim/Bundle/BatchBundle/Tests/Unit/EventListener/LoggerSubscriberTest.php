<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\EventListener;

use Pim\Bundle\BatchBundle\EventListener\LoggerSubscriber;
use Pim\Bundle\BatchBundle\Event\EventInterface;

/**
 * Test related class
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoggerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->logger = $this->getLoggerMock();
        $this->subscriber = new LoggerSubscriber($this->logger);
    }

    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            array(
                EventInterface::BEFORE_JOB_EXECUTION       => 'beforeJobExecution',
                EventInterface::JOB_EXECUTION_STOPPED      => 'jobExecutionStopped',
                EventInterface::JOB_EXECUTION_INTERRUPTED  => 'jobExecutionInterrupted',
                EventInterface::JOB_EXECUTION_FATAL_ERROR  => 'jobExecutionFatalError',
                EventInterface::BEFORE_JOB_STATUS_UPGRADE  => 'beforeJobStatusUpgrade',
                EventInterface::BEFORE_STEP_EXECUTION      => 'beforeStepExecution',
                EventInterface::STEP_EXECUTION_SUCCEED     => 'stepExecutionSucceed',
                EventInterface::STEP_EXECUTION_INTERRUPTED => 'stepExecutionInterrupted',
                EventInterface::STEP_EXECUTION_ERROR       => 'stepExecutionError',
            ),
            LoggerSubscriber::getSubscribedEvents()
        );
    }

    public function testBeforeJobExecution()
    {
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith('Job execution starting'));

        $event = $this->getJobExecutionEventMock();
        $this->subscriber->beforeJobExecution($event);
    }

    public function testJobExecutionStopped()
    {
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith('Job execution was stopped'));

        $event = $this->getJobExecutionEventMock();
        $this->subscriber->jobExecutionStopped($event);
    }

    public function testJobExecutionInterrupted()
    {
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringStartsWith('Encountered interruption executing job'));

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith('Full exception'));

        $jobExecution = $this->getJobExecutionMock();
        $event = $this->getJobExecutionEventMock($jobExecution);
        $this->subscriber->jobExecutionInterrupted($event);
    }

    public function testJobExecutionFatalError()
    {
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringStartsWith('Encountered fatal error executing job'));

        $jobExecution = $this->getJobExecutionMock();
        $event = $this->getJobExecutionEventMock($jobExecution);
        $this->subscriber->jobExecutionFatalError($event);
    }

    public function testBeforeJobStatusUpgrade()
    {
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith('Upgrading JobExecution status'));

        $event = $this->getJobExecutionEventMock();
        $this->subscriber->beforeJobStatusUpgrade($event);
    }

    public function testBeforeStepExecution()
    {
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringStartsWith('Step execution starting'));

        $event = $this->getStepExecutionEventMock();
        $this->subscriber->beforeStepExecution($event);
    }

    public function testStepExecutionInterrupted()
    {
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringStartsWith('Encountered interruption executing step'));

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith('Full exception'));

        $stepExecution = $this->getStepExecutionMock();
        $event = $this->getStepExecutionEventMock($stepExecution);
        $this->subscriber->stepExecutionInterrupted($event);
    }

    public function testStepExecutionError()
    {
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringStartsWith('Encountered an error executing the step'));

        $stepExecution = $this->getStepExecutionMock();
        $event = $this->getStepExecutionEventMock($stepExecution);
        $this->subscriber->stepExecutionError($event);
    }

    private function getLoggerMock()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
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

    private function getStepExecutionEventMock($stepExecution = null)
    {
        $event = $this
            ->getMockBuilder('Pim\Bundle\BatchBundle\Event\StepExecutionEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getStepExecution')
            ->will($this->returnValue($stepExecution));

        return $event;
    }

    private function getJobExecutionMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
